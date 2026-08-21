import './bootstrap';

import { initAnalytics } from './analytics';
import { initSmoothScroll, initScrollAnimations } from './motion';
import { initCursor, initTilt, initSpotlight, initMagnetic, initNav, initScrollSpy, initShowMore } from './ui';

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function boot() {
    // Nav and scroll spy are navigation, not decoration -- always on.
    initNav();
    initScrollSpy();
    initShowMore();

    // Independent of the motion layer: reduced-motion readers still count.
    initAnalytics();

    if (reducedMotion) {
        // Skip smooth scroll and pointer effects entirely, but still draw a
        // static particle field so the hero is not a flat black rectangle.
        initScrollAnimations({ reducedMotion: true });
    } else {
        initSmoothScroll();
        initScrollAnimations({ reducedMotion: false });
        initCursor();
        initTilt();
        initSpotlight();
        initMagnetic();
    }

    const canvas = document.getElementById('bg-canvas');
    if (!canvas) return;

    // Three.js is the bulk of the bundle. Load it as its own chunk so first
    // paint does not wait on it, and drop the canvas if it fails.
    import('./hero-scene')
        .then(({ initHeroScene }) => initHeroScene(canvas, { reducedMotion }))
        .catch((error) => {
            console.warn('Hero scene disabled:', error);
            canvas.remove();
        });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}
