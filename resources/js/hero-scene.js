import * as THREE from 'three';

/**
 * Hero background: a GPU-driven particle field.
 *
 * Points are seeded on a sphere and displaced in the vertex shader by layered
 * noise, so the whole animation runs on the GPU and the CPU only feeds uniforms.
 * The field leans toward the pointer and rotates as the page scrolls.
 */

const vertexShader = /* glsl */ `
    uniform float uTime;
    uniform float uScroll;
    uniform vec2  uPointer;
    uniform float uSize;

    attribute float aScale;
    attribute float aSeed;

    varying float vDepth;
    varying float vSeed;

    // Cheap value noise. Good enough for organic drift, far cheaper than simplex.
    float hash(vec3 p) {
        return fract(sin(dot(p, vec3(127.1, 311.7, 74.7))) * 43758.5453);
    }

    float noise(vec3 p) {
        vec3 i = floor(p);
        vec3 f = fract(p);
        f = f * f * (3.0 - 2.0 * f);

        return mix(
            mix(mix(hash(i + vec3(0,0,0)), hash(i + vec3(1,0,0)), f.x),
                mix(hash(i + vec3(0,1,0)), hash(i + vec3(1,1,0)), f.x), f.y),
            mix(mix(hash(i + vec3(0,0,1)), hash(i + vec3(1,0,1)), f.x),
                mix(hash(i + vec3(0,1,1)), hash(i + vec3(1,1,1)), f.x), f.y),
            f.z
        );
    }

    void main() {
        vec3 pos = position;

        // Two octaves of drift at different speeds keeps it from looking periodic.
        float n1 = noise(pos * 0.35 + vec3(uTime * 0.08, 0.0, 0.0));
        float n2 = noise(pos * 0.9  - vec3(0.0, uTime * 0.12, 0.0));
        pos += normalize(pos) * (n1 * 1.6 + n2 * 0.7);

        // Lean the field toward the pointer, stronger further from the centre.
        float reach = length(pos) * 0.16;
        pos.x += uPointer.x * reach;
        pos.y += uPointer.y * reach;

        // Scrolling pushes the field back and stretches it vertically.
        pos.z -= uScroll * 12.0;
        pos.y += uScroll * 3.0;

        vec4 mvPosition = modelViewMatrix * vec4(pos, 1.0);

        vSeed = aSeed;
        vDepth = clamp(-mvPosition.z / 40.0, 0.0, 1.0);

        gl_PointSize = uSize * aScale * (30.0 / -mvPosition.z);
        gl_Position = projectionMatrix * mvPosition;
    }
`;

const fragmentShader = /* glsl */ `
    uniform vec3 uColorA;
    uniform vec3 uColorB;
    uniform float uOpacity;

    varying float vDepth;
    varying float vSeed;

    void main() {
        // Round the square point sprite into a soft dot.
        vec2 uv = gl_PointCoord - 0.5;
        float d = length(uv);
        if (d > 0.5) discard;

        float alpha = smoothstep(0.5, 0.05, d);

        // Mix the two accent colours per particle, then fade with distance.
        vec3 color = mix(uColorA, uColorB, vSeed);
        alpha *= (1.0 - vDepth) * uOpacity;

        gl_FragColor = vec4(color, alpha);
    }
`;

export function initHeroScene(canvas, { reducedMotion = false } = {}) {
    // Bail out cleanly on anything that cannot render this.
    const gl = canvas.getContext('webgl2') || canvas.getContext('webgl');
    if (!gl) return null;

    const isMobile = window.matchMedia('(max-width: 768px)').matches;
    const count = isMobile ? 2200 : 6000;

    const scene = new THREE.Scene();
    scene.fog = new THREE.FogExp2(0x05060a, 0.022);

    const camera = new THREE.PerspectiveCamera(60, canvas.clientWidth / canvas.clientHeight, 0.1, 120);
    camera.position.z = 26;

    const renderer = new THREE.WebGLRenderer({
        canvas,
        antialias: false,
        alpha: true,
        powerPreference: 'high-performance',
    });
    renderer.setClearColor(0x000000, 0);
    // Cap DPR: past 2x the cost doubles for no visible gain on a particle field.
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

    // --- Geometry -----------------------------------------------------------

    const positions = new Float32Array(count * 3);
    const scales = new Float32Array(count);
    const seeds = new Float32Array(count);

    for (let i = 0; i < count; i++) {
        // Even distribution across a spherical shell.
        const theta = Math.random() * Math.PI * 2;
        const phi = Math.acos(2 * Math.random() - 1);
        const radius = 9 + Math.random() * 11;

        positions[i * 3] = radius * Math.sin(phi) * Math.cos(theta);
        positions[i * 3 + 1] = radius * Math.sin(phi) * Math.sin(theta);
        positions[i * 3 + 2] = radius * Math.cos(phi);

        scales[i] = 0.4 + Math.random() * 1.6;
        seeds[i] = Math.random();
    }

    const geometry = new THREE.BufferGeometry();
    geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    geometry.setAttribute('aScale', new THREE.BufferAttribute(scales, 1));
    geometry.setAttribute('aSeed', new THREE.BufferAttribute(seeds, 1));

    const uniforms = {
        uTime: { value: 0 },
        uScroll: { value: 0 },
        uPointer: { value: new THREE.Vector2(0, 0) },
        uSize: { value: isMobile ? 2.2 : 3.0 },
        uColorA: { value: new THREE.Color('#38f0d4') },
        uColorB: { value: new THREE.Color('#8b5cf6') },
        uOpacity: { value: 0 }, // faded in on first frame
    };

    const points = new THREE.Points(
        geometry,
        new THREE.ShaderMaterial({
            vertexShader,
            fragmentShader,
            uniforms,
            transparent: true,
            depthWrite: false,
            blending: THREE.AdditiveBlending,
        })
    );
    scene.add(points);

    // --- State --------------------------------------------------------------

    const pointer = new THREE.Vector2(0, 0);
    const target = new THREE.Vector2(0, 0);
    let scrollProgress = 0;
    let running = true;
    let frame = null;
    const clock = new THREE.Clock();

    function resize() {
        const w = canvas.clientWidth || window.innerWidth;
        const h = canvas.clientHeight || window.innerHeight;

        camera.aspect = w / h;
        camera.updateProjectionMatrix();
        renderer.setSize(w, h, false);
    }

    function onPointerMove(event) {
        // Normalised to -1..1 with the origin at the centre of the viewport.
        target.x = (event.clientX / window.innerWidth) * 2 - 1;
        target.y = -((event.clientY / window.innerHeight) * 2 - 1);
    }

    function onScroll() {
        const max = document.body.scrollHeight - window.innerHeight;
        scrollProgress = max > 0 ? window.scrollY / max : 0;
    }

    function render() {
        frame = requestAnimationFrame(render);
        if (!running) return;

        const elapsed = clock.getElapsedTime();

        // Ease pointer and scroll so nothing snaps.
        pointer.lerp(target, 0.05);
        uniforms.uPointer.value.copy(pointer);
        uniforms.uScroll.value += (scrollProgress - uniforms.uScroll.value) * 0.06;
        uniforms.uTime.value = reducedMotion ? 0 : elapsed;
        uniforms.uOpacity.value = Math.min(uniforms.uOpacity.value + 0.012, 0.85);

        if (!reducedMotion) {
            points.rotation.y = elapsed * 0.03 + pointer.x * 0.25;
            points.rotation.x = pointer.y * 0.2;
        }

        renderer.render(scene, camera);
    }

    // --- Wiring -------------------------------------------------------------

    resize();
    render();
    onScroll();

    window.addEventListener('resize', resize);
    window.addEventListener('scroll', onScroll, { passive: true });

    // Pointer parallax is a desktop affordance; skip the listener on touch.
    if (!isMobile) {
        window.addEventListener('pointermove', onPointerMove, { passive: true });
    }

    // Stop burning frames when the tab is in the background.
    document.addEventListener('visibilitychange', () => {
        running = !document.hidden;
        if (running) clock.getDelta();
    });

    return {
        destroy() {
            cancelAnimationFrame(frame);
            window.removeEventListener('resize', resize);
            window.removeEventListener('scroll', onScroll);
            window.removeEventListener('pointermove', onPointerMove);
            geometry.dispose();
            points.material.dispose();
            renderer.dispose();
        },
    };
}
