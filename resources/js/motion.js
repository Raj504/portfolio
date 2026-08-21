import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import Lenis from 'lenis';

gsap.registerPlugin(ScrollTrigger);

/**
 * Smooth scrolling. Lenis takes over the scroll position, so ScrollTrigger has
 * to be told about it or the two disagree about where the page is.
 */
export function initSmoothScroll() {
    const lenis = new Lenis({
        duration: 1.1,
        easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
        smoothWheel: true,
    });

    lenis.on('scroll', ScrollTrigger.update);

    gsap.ticker.add((time) => lenis.raf(time * 1000));
    gsap.ticker.lagSmoothing(0);

    // In-page anchors need to go through Lenis to stay smooth.
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener('click', (event) => {
            const id = anchor.getAttribute('href');
            if (id === '#') return;

            const target = document.querySelector(id);
            if (!target) return;

            event.preventDefault();
            lenis.scrollTo(target, { offset: -80 });
        });
    });

    return lenis;
}

/**
 * Split the hero headline into per-character spans and stagger them in.
 */
function animateHeadline() {
    const lines = document.querySelectorAll('[data-split-line]');
    if (!lines.length) return;

    const targets = [];

    lines.forEach((line) => {
        // Lines marked "whole" animate as a single block. Splitting them would
        // break effects that need the line to stay one box -- notably the
        // gradient headline, since background-clip: text does not extend into
        // inline-block children.
        if (line.dataset.splitLine === 'whole') {
            targets.push(line);
            return;
        }

        const text = line.textContent.trim();

        // Rebuild the line out of spans. aria-label keeps it readable to
        // screen readers, which would otherwise announce it letter by letter.
        line.setAttribute('aria-label', text);
        line.textContent = '';

        [...text].forEach((char) => {
            const span = document.createElement('span');
            span.className = 'inline-block';
            span.textContent = char === ' ' ? ' ' : char;
            span.setAttribute('aria-hidden', 'true');
            line.appendChild(span);
            targets.push(span);
        });
    });

    gsap.from(targets, {
        yPercent: 115,
        opacity: 0,
        duration: 1.1,
        ease: 'expo.out',
        stagger: { each: 0.022, from: 'start' },
        delay: 0.15,
    });
}

/**
 * Scroll-triggered reveals for anything tagged data-reveal.
 */
function animateReveals() {
    document.querySelectorAll('[data-reveal]').forEach((el) => {
        const mode = el.dataset.reveal;

        gsap.fromTo(
            el,
            {
                opacity: 0,
                y: mode === 'up' ? 44 : 0,
            },
            {
                opacity: 1,
                y: 0,
                duration: 1,
                ease: 'expo.out',
                scrollTrigger: {
                    trigger: el,
                    start: 'top 88%',
                    once: true,
                },
            }
        );
    });
}

/**
 * The timeline rail draws itself as the section passes through the viewport.
 */
function animateTimeline() {
    const fill = document.getElementById('timeline-fill');
    if (!fill) return;

    gsap.to(fill, {
        scaleY: 1,
        ease: 'none',
        scrollTrigger: {
            trigger: fill.parentElement,
            start: 'top 70%',
            end: 'bottom 80%',
            scrub: 0.6,
        },
    });
}

/**
 * Top progress bar, tied to overall document scroll.
 */
function animateProgressBar() {
    const bar = document.getElementById('scroll-progress');
    if (!bar) return;

    gsap.to(bar, {
        scaleX: 1,
        ease: 'none',
        scrollTrigger: {
            trigger: document.body,
            start: 'top top',
            end: 'bottom bottom',
            scrub: 0.3,
        },
    });
}

/**
 * Sections drift slightly slower than the page, adding depth against the
 * WebGL field behind them.
 */
function animateParallax() {
    gsap.utils.toArray('section').forEach((section) => {
        const bloom = section.querySelector('[aria-hidden="true"].blur-\\[140px\\]');
        if (!bloom) return;

        gsap.to(bloom, {
            yPercent: -18,
            ease: 'none',
            scrollTrigger: {
                trigger: section,
                start: 'top bottom',
                end: 'bottom top',
                scrub: true,
            },
        });
    });
}

export function initScrollAnimations({ reducedMotion = false } = {}) {
    if (reducedMotion) {
        // Everything is already visible via the CSS fallback; just show the bar.
        gsap.set('#scroll-progress', { scaleX: 1, opacity: 0.4 });
        return;
    }

    animateHeadline();
    animateReveals();
    animateTimeline();
    animateProgressBar();
    animateParallax();

    // Fonts landing late shift layout and stale trigger positions cause
    // elements to reveal at the wrong scroll point.
    document.fonts?.ready.then(() => ScrollTrigger.refresh());
}
