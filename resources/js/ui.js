import gsap from 'gsap';

const isTouch = window.matchMedia('(hover: none)').matches;

/**
 * Two-part cursor: a dot that tracks exactly, and a ring that lags behind and
 * swells over interactive elements. Pointer devices only.
 */
export function initCursor() {
    if (isTouch) return;

    const dot = document.getElementById('cursor-dot');
    const ring = document.getElementById('cursor-ring');
    if (!dot || !ring) return;

    // quickTo writes straight to the transform without rebuilding a tween.
    const dotX = gsap.quickTo(dot, 'x', { duration: 0.12, ease: 'power3.out' });
    const dotY = gsap.quickTo(dot, 'y', { duration: 0.12, ease: 'power3.out' });
    const ringX = gsap.quickTo(ring, 'x', { duration: 0.45, ease: 'power3.out' });
    const ringY = gsap.quickTo(ring, 'y', { duration: 0.45, ease: 'power3.out' });

    gsap.set([dot, ring], { xPercent: -50, yPercent: -50, top: 0, left: 0 });

    window.addEventListener('pointermove', (event) => {
        dotX(event.clientX);
        dotY(event.clientY);
        ringX(event.clientX);
        ringY(event.clientY);
    }, { passive: true });

    const interactive = 'a, button, [data-tilt], input, textarea';

    document.addEventListener('pointerover', (event) => {
        if (event.target.closest(interactive)) {
            gsap.to(ring, { scale: 1.8, opacity: 0.5, duration: 0.35, ease: 'expo.out' });
        }
    });

    document.addEventListener('pointerout', (event) => {
        if (event.target.closest(interactive)) {
            gsap.to(ring, { scale: 1, opacity: 1, duration: 0.35, ease: 'expo.out' });
        }
    });
}

/**
 * Perspective tilt on cards, driven by pointer position within the card.
 */
export function initTilt() {
    bindTilt(document.querySelectorAll('[data-tilt]'));
}

/**
 * Bind tilt to a specific set of cards. Split out from initTilt so cards
 * revealed after boot can be wired up too. Guards against double-binding.
 */
function bindTilt(cards) {
    if (isTouch) return;

    cards.forEach((card) => {
        if (card.dataset.tiltBound) return;
        card.dataset.tiltBound = '1';

        const rotX = gsap.quickTo(card, 'rotationX', { duration: 0.6, ease: 'power3.out' });
        const rotY = gsap.quickTo(card, 'rotationY', { duration: 0.6, ease: 'power3.out' });

        gsap.set(card, { transformPerspective: 1000, transformOrigin: 'center' });

        card.addEventListener('pointermove', (event) => {
            const rect = card.getBoundingClientRect();
            const px = (event.clientX - rect.left) / rect.width - 0.5;
            const py = (event.clientY - rect.top) / rect.height - 0.5;

            rotY(px * 9);
            rotX(-py * 9);
        });

        card.addEventListener('pointerleave', () => {
            rotX(0);
            rotY(0);
        });
    });
}

/**
 * Feed pointer position into the CSS spotlight gradient.
 */
export function initSpotlight() {
    bindSpotlight(document.querySelectorAll('[data-spotlight]'));
}

function bindSpotlight(elements) {
    if (isTouch) return;

    elements.forEach((el) => {
        if (el.dataset.spotlightBound) return;
        el.dataset.spotlightBound = '1';

        el.addEventListener('pointermove', (event) => {
            const rect = el.getBoundingClientRect();
            el.style.setProperty('--mx', `${event.clientX - rect.left}px`);
            el.style.setProperty('--my', `${event.clientY - rect.top}px`);
        });
    });
}

/**
 * Buttons that pull slightly toward the cursor when it comes close.
 */
export function initMagnetic() {
    if (isTouch) return;

    document.querySelectorAll('[data-magnetic]').forEach((el) => {
        const moveX = gsap.quickTo(el, 'x', { duration: 0.5, ease: 'power3.out' });
        const moveY = gsap.quickTo(el, 'y', { duration: 0.5, ease: 'power3.out' });

        el.addEventListener('pointermove', (event) => {
            const rect = el.getBoundingClientRect();
            moveX((event.clientX - (rect.left + rect.width / 2)) * 0.3);
            moveY((event.clientY - (rect.top + rect.height / 2)) * 0.3);
        });

        el.addEventListener('pointerleave', () => {
            moveX(0);
            moveY(0);
        });
    });
}

/**
 * Reveal the collapsed tail of the project grid.
 *
 * The extra cards are rendered server-side but carry `hidden`, so they cost
 * nothing until asked for and remain in the HTML for crawlers.
 */
export function initShowMore() {
    const button = document.getElementById('show-more-projects');
    if (!button) return;

    const extras = document.querySelectorAll('[data-project-extra]');
    if (! extras.length) return;

    const label = button.querySelector('[data-show-more-label]');
    const icon = button.querySelector('[data-show-more-icon]');
    let expanded = false;

    button.addEventListener('click', () => {
        expanded = ! expanded;

        label.textContent = expanded ? button.dataset.labelLess : button.dataset.labelMore;
        icon.style.transform = expanded ? 'rotate(180deg)' : 'rotate(0deg)';

        if (expanded) {
            extras.forEach((card) => card.classList.remove('hidden'));

            gsap.fromTo(
                extras,
                { opacity: 0, y: 40 },
                { opacity: 1, y: 0, duration: 0.8, ease: 'expo.out', stagger: 0.06 }
            );

            // The tilt handlers are bound once at boot, so cards revealed
            // afterwards need wiring up themselves.
            bindTilt(extras);
            bindSpotlight(extras);
        } else {
            gsap.to(extras, {
                opacity: 0,
                y: 20,
                duration: 0.3,
                ease: 'power2.in',
                onComplete: () => extras.forEach((card) => card.classList.add('hidden')),
            });

            button.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
}

/**
 * Nav: frosted background once scrolled, and the mobile drawer.
 */
export function initNav() {
    const nav = document.getElementById('site-nav');
    const toggle = document.getElementById('menu-toggle');
    const menu = document.getElementById('mobile-menu');

    if (nav) {
        const solid = ['border-edge/60', 'bg-void/80', 'backdrop-blur-xl'];

        const update = () => {
            nav.classList.toggle('border-transparent', window.scrollY <= 40);
            solid.forEach((c) => nav.classList.toggle(c, window.scrollY > 40));
        };

        update();
        window.addEventListener('scroll', update, { passive: true });
    }

    if (toggle && menu) {
        const bars = toggle.querySelectorAll('.menu-bar');
        let open = false;

        const setOpen = (next) => {
            open = next;
            toggle.setAttribute('aria-expanded', String(open));
            toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');

            menu.classList.toggle('max-h-0', !open);
            menu.classList.toggle('max-h-96', open);
            menu.classList.toggle('pointer-events-none', !open);
            menu.classList.toggle('border-edge/0', !open);
            menu.classList.toggle('border-edge', open);

            // Cross the two bars into an X.
            gsap.to(bars[0], { y: open ? 6 : 0, rotate: open ? 45 : 0, duration: 0.3 });
            gsap.to(bars[1], { y: open ? -6 : 0, rotate: open ? -45 : 0, duration: 0.3 });
        };

        toggle.addEventListener('click', () => setOpen(!open));
        menu.querySelectorAll('a').forEach((a) => a.addEventListener('click', () => setOpen(false)));
        document.addEventListener('keydown', (e) => e.key === 'Escape' && open && setOpen(false));
    }
}

/**
 * Highlight the nav link for whichever section is currently on screen.
 */
export function initScrollSpy() {
    const links = document.querySelectorAll('[data-nav-link]');
    if (!links.length) return;

    const sections = [...links]
        .map((link) => document.getElementById(link.dataset.navLink))
        .filter(Boolean);

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;

                links.forEach((link) => {
                    const active = link.dataset.navLink === entry.target.id;
                    link.classList.toggle('text-cyan-glow', active);
                    link.classList.toggle('text-muted', !active);
                });
            });
        },
        // Only fire for the section crossing the middle band of the viewport.
        { rootMargin: '-45% 0px -45% 0px' }
    );

    sections.forEach((section) => observer.observe(section));
}
