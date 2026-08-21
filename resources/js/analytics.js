/**
 * First-party analytics.
 *
 * Measures active reading time per section, clicks on outbound and contact
 * links, and scroll depth. Nothing leaves this domain and no persistent
 * identifier is stored: the session id lives in sessionStorage, so closing
 * the tab ends it.
 *
 * Time only accrues while the tab is visible and the section is genuinely on
 * screen, so a page left open in a background tab does not report hours of
 * "reading".
 */

const ENDPOINT = '/track';
const SESSION_KEY = 'pf_sid';

/* How often to send, while the reader is actually active. */
const FLUSH_INTERVAL = 45000;

/* No pointer, key, scroll or touch for this long and the reader is treated as
   gone: timers pause and beacons stop until they come back. */
const IDLE_TIMEOUT = 60000;

/* How often to test for idleness. Local only -- costs no network. */
const IDLE_CHECK_INTERVAL = 5000;

/* Absolute ceiling on one page session. A tab forgotten in a background
   window must not keep beaconing all day. */
const MAX_SESSION = 1800000;

/** Fraction of a section that must be visible before its timer runs. */
const VISIBLE_RATIO = 0.4;

function sessionId() {
    let id = sessionStorage.getItem(SESSION_KEY);

    if (!id) {
        // 32 hex chars, matching the server's size:32|alpha_num rule.
        const bytes = crypto.getRandomValues(new Uint8Array(16));
        id = [...bytes].map((b) => b.toString(16).padStart(2, '0')).join('');
        sessionStorage.setItem(SESSION_KEY, id);
    }

    return id;
}

export function initAnalytics() {
    // Respect the browser's opt-out signals before doing anything at all.
    if (navigator.doNotTrack === '1' || navigator.globalPrivacyControl === true) {
        return;
    }

    if (!('IntersectionObserver' in window) || !window.crypto?.getRandomValues) {
        return;
    }

    // The layout emits this for a signed-in admin so the owner's own reading
    // never becomes data -- and never becomes a request either.
    if (document.querySelector('meta[name="analytics"]')?.content === 'off') {
        return;
    }

    const sid = sessionId();

    // --- state --------------------------------------------------------------

    const sectionTotals = new Map();  // section id -> seconds accrued
    let sentSectionTotals = new Map(); // what the server already knows
    const pendingClicks = [];
    const sentDepths = new Set();

    let maxScroll = 0;
    let activeSection = null;
    let activeSince = null;
    let totalActive = 0;
    let pageActiveSince = performance.now();

    // Idle / lifecycle state.
    let lastActivity = performance.now();
    let idle = false;
    let stopped = false;
    let flushTimer = null;
    let idleTimer = null;
    const startedAt = performance.now();

    // --- timing -------------------------------------------------------------

    function closeSection(now = performance.now()) {
        if (activeSection && activeSince !== null) {
            const seconds = (now - activeSince) / 1000;
            sectionTotals.set(activeSection, (sectionTotals.get(activeSection) || 0) + seconds);
        }

        activeSection = null;
        activeSince = null;
    }

    function openSection(id, now = performance.now()) {
        if (activeSection === id) return;
        closeSection(now);
        activeSection = id;
        activeSince = now;
    }

    /**
     * Stop the clocks. `at` lets the caller rewind to when the reader was last
     * actually doing something, so an idle stretch is never counted as reading.
     */
    function pauseTiming(at = performance.now()) {
        const end = Math.max(at, pageActiveSince);
        closeSection(end);
        totalActive += (end - pageActiveSince) / 1000;
        pageActiveSince = end;
    }

    function resumeTiming() {
        pageActiveSince = performance.now();
    }

    function activeSeconds() {
        const now = performance.now();
        return Math.round(totalActive + (document.hidden ? 0 : (now - pageActiveSince) / 1000));
    }

    // --- observers ----------------------------------------------------------

    const sections = document.querySelectorAll('main section[id]');
    let mostVisible = null;

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                entry.target.dataset.ratio = entry.intersectionRatio;
            });

            // Only one section counts at a time: whichever fills the most of
            // the viewport. Otherwise overlapping sections double-count.
            let best = null;
            let bestRatio = VISIBLE_RATIO;

            sections.forEach((section) => {
                const ratio = parseFloat(section.dataset.ratio || '0');
                if (ratio > bestRatio) {
                    bestRatio = ratio;
                    best = section.id;
                }
            });

            mostVisible = best;

            if (document.hidden || !best) {
                closeSection();
            } else {
                openSection(best);
            }
        },
        { threshold: [0, 0.25, 0.4, 0.6, 0.8, 1] }
    );

    sections.forEach((section) => observer.observe(section));

    // --- scroll depth -------------------------------------------------------

    function trackScroll() {
        const scrollable = document.body.scrollHeight - window.innerHeight;
        if (scrollable <= 0) return;

        const percent = Math.min(100, Math.round((window.scrollY / scrollable) * 100));
        maxScroll = Math.max(maxScroll, percent);

        [25, 50, 75, 100].forEach((milestone) => {
            if (maxScroll >= milestone && !sentDepths.has(milestone)) {
                sentDepths.add(milestone);
                pendingClicks.push({ type: 'scroll_depth', target: String(milestone), value: milestone });
            }
        });
    }

    window.addEventListener('scroll', trackScroll, { passive: true });

    // --- clicks -------------------------------------------------------------

    /**
     * Label a click in a way that reads well on the dashboard. Project links
     * are tagged "project:<title>:<live|repo>" so they can be grouped.
     */
    function labelFor(link) {
        const card = link.closest('article');
        const title = card?.querySelector('h3')?.textContent?.trim();

        if (title && link.hasAttribute('aria-label')) {
            const label = link.getAttribute('aria-label');
            if (label.includes('live site')) return `project:${title}:live`;
            if (label.includes('source code')) return `project:${title}:repo`;
        }

        const href = link.getAttribute('href') || '';

        if (href.startsWith('mailto:')) return 'contact:email';
        if (href.startsWith('tel:')) return 'contact:phone';
        if (href.startsWith('#')) return `nav:${href.slice(1)}`;

        try {
            const url = new URL(href, window.location.origin);
            if (url.origin !== window.location.origin) {
                return `outbound:${url.hostname.replace(/^www\./, '')}`;
            }
        } catch {
            // Malformed href; fall through to the text label.
        }

        return `link:${(link.textContent || 'unknown').trim().slice(0, 60)}`;
    }

    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[href]');

        if (link) {
            pendingClicks.push({ type: 'click', target: labelFor(link), value: 0 });

            // Leaving the page: get the beacon out now.
            if (link.target === '_blank' || !link.getAttribute('href').startsWith('#')) {
                flush();
            }

            return;
        }

        const button = event.target.closest('button[id]');
        if (button) {
            pendingClicks.push({ type: 'click', target: `button:${button.id}`, value: 0 });
        }
    });

    // --- sending ------------------------------------------------------------

    function buildPayload() {
        // Section timing is cumulative, so send only what has accrued since
        // the last beacon. The server sums the deltas.
        const events = [];

        sectionTotals.forEach((seconds, id) => {
            const already = sentSectionTotals.get(id) || 0;
            const delta = Math.round(seconds) - already;

            if (delta > 0) {
                events.push({ type: 'section_time', target: id, value: delta });
            }
        });

        events.push(...pendingClicks);

        if (!events.length && sentAnything) {
            return null;
        }

        return {
            sid,
            path: window.location.pathname,
            referrer: document.referrer || null,
            screen: `${window.screen.width}x${window.screen.height}`,
            duration: activeSeconds(),
            max_scroll: maxScroll,
            events,
        };
    }

    let sentAnything = false;

    function commit() {
        // Record what was just sent so the next delta is correct.
        sentSectionTotals = new Map(
            [...sectionTotals].map(([id, seconds]) => [id, Math.round(seconds)])
        );
        pendingClicks.length = 0;
        sentAnything = true;
    }

    function flush() {
        // Fold in time accrued so far without stopping the clock.
        const now = performance.now();
        if (activeSection && activeSince !== null) {
            sectionTotals.set(
                activeSection,
                (sectionTotals.get(activeSection) || 0) + (now - activeSince) / 1000
            );
            activeSince = now;
        }

        const payload = buildPayload();
        if (!payload) return;

        const body = JSON.stringify(payload);

        // sendBeacon survives the page being closed; fetch does not.
        if (navigator.sendBeacon) {
            const blob = new Blob([body], { type: 'application/json' });
            if (navigator.sendBeacon(ENDPOINT, blob)) {
                commit();
                return;
            }
        }

        fetch(ENDPOINT, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body,
            keepalive: true,
        }).then(commit).catch(() => {});
    }

    // --- lifecycle ----------------------------------------------------------

    /**
     * Schedule the next beacon. Deliberately a self-rescheduling timeout rather
     * than setInterval: when the reader goes idle the chain simply stops, and
     * activity restarts it. An idle tab therefore sends nothing at all.
     */
    function scheduleFlush() {
        clearTimeout(flushTimer);
        if (stopped || idle) return;

        flushTimer = setTimeout(() => {
            flush();
            scheduleFlush();
        }, FLUSH_INTERVAL);
    }

    function goIdle() {
        if (idle || stopped) return;
        idle = true;

        // Rewind to the last real interaction so the idle gap is not counted.
        pauseTiming(lastActivity);
        clearTimeout(flushTimer);
        flush();
    }

    function wake() {
        if (stopped) return;

        lastActivity = performance.now();
        if (!idle) return;

        idle = false;
        resumeTiming();
        if (!document.hidden && mostVisible) openSection(mostVisible);
        scheduleFlush();
    }

    /** Send a final beacon and never send another. */
    function stop() {
        if (stopped) return;
        pauseTiming(idle ? lastActivity : undefined);
        flush();
        stopped = true;
        clearTimeout(flushTimer);
        clearInterval(idleTimer);
    }

    // Any of these means a human is still there. The handler is a timestamp
    // write, so firing on every pointermove costs nothing measurable.
    ['pointerdown', 'pointermove', 'keydown', 'scroll', 'touchstart', 'wheel']
        .forEach((type) => window.addEventListener(type, wake, { passive: true }));

    idleTimer = setInterval(() => {
        if (stopped) return;

        if (performance.now() - startedAt > MAX_SESSION) {
            stop();
            return;
        }

        if (!idle && performance.now() - lastActivity > IDLE_TIMEOUT) {
            goIdle();
        }
    }, IDLE_CHECK_INTERVAL);

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            pauseTiming();
            clearTimeout(flushTimer);
            flush();
        } else {
            resumeTiming();
            lastActivity = performance.now();
            idle = false;
            if (mostVisible) openSection(mostVisible);
            scheduleFlush();
        }
    });

    window.addEventListener('pagehide', stop);

    trackScroll();

    // Opening beacon so a visit is recorded even if the reader leaves fast,
    // then settle into the normal cadence.
    setTimeout(() => {
        flush();
        scheduleFlush();
    }, 1500);
}
