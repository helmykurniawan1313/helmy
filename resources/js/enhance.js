import { animate, inView, stagger } from 'motion';
import NumberFlow, { define } from 'number-flow';

const reducedMotion = () => matchMedia('(prefers-reduced-motion: reduce)').matches;

// Critically damped spring: no overshoot for content that simply appears
const SETTLE = { type: 'spring', bounce: 0, duration: 0.7 };

/**
 * Scroll reveals with Motion. Grid children enter as a group with a
 * reading-order stagger; standalone elements reveal on their own.
 */
export function initReveal() {
    const groups = document.querySelectorAll('.bento, .svc-row, .proj-row, .role-grid, .role-rail, .about-facts, .svc-list');
    const grouped = new Set();

    groups.forEach((group) => {
        const items = [...group.querySelectorAll(':scope > .reveal')];
        items.forEach((el) => grouped.add(el));
        if (!items.length) return;

        inView(group, () => {
            reveal(items, stagger(0.06));
        }, { amount: 0.15 });
    });

    document.querySelectorAll('.reveal').forEach((el) => {
        if (grouped.has(el)) return;
        inView(el, () => reveal(el, 0), { amount: 0.2 });
    });
}

function reveal(target, delay) {
    if (reducedMotion()) {
        // Cross-fade only; no vestibular movement
        return animate(target, { opacity: 1, transform: 'none' }, { duration: 0.3, delay });
    }
    return animate(
        target,
        { opacity: [0, 1], transform: ['translateY(48px)', 'translateY(0px)'], filter: ['blur(8px)', 'blur(0px)'] },
        { duration: 0.9, ease: [0.32, 0.72, 0, 1], delay },
    );
}

/**
 * NumberFlow digit roll for stats. The plain number stays in the
 * server-rendered HTML (for SEO / no-JS); it is swapped for a
 * <number-flow> element that rolls up when scrolled into view.
 */
export function initNumbers() {
    define('number-flow', NumberFlow);

    document.querySelectorAll('[data-flow]').forEach((el) => {
        const value = parseFloat(el.dataset.flow);
        const decimals = parseInt(el.dataset.flowDecimals || '0', 10);

        const flow = document.createElement('number-flow');
        flow.format = { minimumFractionDigits: decimals, maximumFractionDigits: decimals };
        flow.numberSuffix = el.dataset.flowSuffix || '';
        flow.setAttribute('aria-label', el.textContent.trim());

        if (reducedMotion()) {
            flow.animated = false;
            flow.update(value);
            el.replaceWith(flow);
            return;
        }

        flow.update(0);
        el.replaceWith(flow);
        inView(flow, () => { flow.update(value); }, { amount: 0.6 });
    });
}

/**
 * Cobe globe in the Location tile, centred on Surabaya.
 * Follows the light/dark theme; renders only while visible.
 */
export function initGlobe() {
    const canvas = document.querySelector('[data-globe]');
    if (!canvas) return;

    const SURABAYA = [-7.2575, 112.7521];
    const [phi, theta] = [
        Math.PI - ((SURABAYA[1] * Math.PI) / 180 - Math.PI / 2),
        (SURABAYA[0] * Math.PI) / 180,
    ];

    const isDark = () => document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const palette = () => (isDark()
        ? { dark: 1, baseColor: [0.3, 0.3, 0.35], glowColor: [0.15, 0.15, 0.2], mapBrightness: 6 }
        : { dark: 0, baseColor: [1, 1, 1], glowColor: [0.92, 0.94, 1], mapBrightness: 4 });

    let globe;
    const dpr = Math.min(window.devicePixelRatio || 1, 2);

    const mount = async () => {
        const { default: createGlobe } = await import('cobe');
        const size = canvas.offsetWidth;
        globe = createGlobe(canvas, {
            devicePixelRatio: dpr,
            width: size * dpr,
            height: size * dpr,
            phi,
            theta: theta + 0.25,
            diffuse: 1.2,
            mapSamples: 12000,
            markerColor: [0.26, 0.37, 0.75],
            markers: [{ location: SURABAYA, size: 0.09 }],
            ...palette(),
        });
        animate(canvas, { opacity: [0, 1] }, { duration: 0.6 });
    };

    // Lazy: only build the WebGL context when the tile is near the viewport
    inView(canvas, () => {
        if (!globe) mount();
    }, { margin: '200px' });

    // Keep in sync with the theme toggle
    new MutationObserver(() => globe?.update(palette()))
        .observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });
}

// Slow settle-in zoom on the photo banner when it enters the viewport
export function initPhotoBanner() {
    document.querySelectorAll('.photo-banner').forEach((el) => {
        inView(el, () => { el.classList.add('in-view'); }, { amount: 0.25 });
    });
}

/**
 * Scroll-tied background colour (Apple Wallet style).
 * Each [data-scroll-bg] section holds a coloured card whose colour and
 * text never change. As the section scrolls in (its top travelling from
 * 55% of the viewport to the top), the section's own background blends
 * from the page colour to the card colour, so the card's edges dissolve
 * and it "absorbs" into the page.
 *
 * Performance: the rAF loop runs only while the blend is still moving,
 * restarts on scroll/resize, and writes styles only when they change.
 */
export function initScrollBg() {
    const sections = [...document.querySelectorAll('[data-scroll-bg]')];
    if (!sections.length) return;

    const parseColor = (str) => {
        const s = (str || '').trim();
        const hex = s.match(/^#([0-9a-f]{3}|[0-9a-f]{6})$/i);
        if (hex) {
            let h = hex[1];
            if (h.length === 3) h = [...h].map((c) => c + c).join('');
            return [0, 2, 4].map((i) => parseInt(h.slice(i, i + 2), 16));
        }
        const rgb = s.match(/(\d+(?:\.\d+)?)[,\s]+(\d+(?:\.\d+)?)[,\s]+(\d+(?:\.\d+)?)/);
        return rgb ? [Number(rgb[1]), Number(rgb[2]), Number(rgb[3])] : [255, 255, 255];
    };

    const mix = (a, b, t) => `rgb(${a.map((v, i) => Math.round(v + (b[i] - v) * t)).join(', ')})`;
    const isDark = () => document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const pageColor = () => parseColor(getComputedStyle(document.body).backgroundColor);

    const state = sections.map((el) => ({
        el,
        current: 0,
        visible: false,
        painted: '',
        from: [255, 255, 255],
        to: [255, 255, 255],
    }));

    const progress = (el) => {
        const vh = window.innerHeight;
        const startAt = 0.55;
        const raw = (vh * startAt - el.getBoundingClientRect().top) / (vh * startAt);
        return Math.min(1, Math.max(0, raw));
    };

    const paint = (s) => {
        const color = mix(s.from, s.to, s.current);
        if (color !== s.painted) {
            s.el.style.backgroundColor = color;
            s.painted = color;
        }
    };

    const ease = 0.18;
    let running = false;

    const tick = () => {
        let moving = false;
        state.forEach((s) => {
            if (!s.visible) return;
            const target = progress(s.el);
            if (reducedMotion()) {
                s.current = target;
            } else {
                s.current += (target - s.current) * ease;
                if (Math.abs(target - s.current) < 0.002) s.current = target;
            }
            paint(s);
            if (s.current !== target) moving = true;
        });
        running = moving;
        if (moving) requestAnimationFrame(tick);
    };

    const kick = () => {
        if (!running) {
            running = true;
            requestAnimationFrame(tick);
        }
    };

    // Jump straight to the right colour (first load, theme change)
    const snap = () => {
        state.forEach((s) => {
            s.current = progress(s.el);
            s.painted = '';
            paint(s);
        });
    };

    const resync = () => {
        const from = pageColor();
        state.forEach((s) => {
            s.from = from;
            s.to = parseColor(isDark() && s.el.dataset.scrollBgDark ? s.el.dataset.scrollBgDark : s.el.dataset.scrollBg);
        });
        snap();
    };

    const io = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            const s = state.find((x) => x.el === entry.target);
            if (s) s.visible = entry.isIntersecting;
        });
        kick();
    }, { rootMargin: '20% 0px' });
    sections.forEach((el) => io.observe(el));

    window.addEventListener('scroll', kick, { passive: true });
    window.addEventListener('resize', kick, { passive: true });

    new MutationObserver(() => setTimeout(resync, 420))
        .observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });

    resync();
}

/**
 * Wallet-style expanding colour panel.
 * [data-scroll-expand] sections expose their scroll progress as the CSS
 * custom property --p (0 → 1). CSS uses it to grow a blue panel from the
 * left column to the full section and to blend the list text to white.
 * Same progress window as initScrollBg; the loop idles when settled.
 */
export function initScrollExpand() {
    const sections = [...document.querySelectorAll('[data-scroll-expand]')];
    if (!sections.length) return;

    const progress = (el) => {
        const vh = window.innerHeight;
        const startAt = 0.6;
        const raw = (vh * startAt - el.getBoundingClientRect().top) / (vh * startAt);
        return Math.min(1, Math.max(0, raw));
    };

    const state = sections.map((el) => ({ el, current: progress(el), visible: false, painted: '' }));
    const paint = (s) => {
        const v = s.current.toFixed(3);
        if (v !== s.painted) {
            s.el.style.setProperty('--p', v);
            s.painted = v;
        }
    };
    state.forEach(paint);

    const ease = 0.16;
    let running = false;

    const tick = () => {
        let moving = false;
        state.forEach((s) => {
            if (!s.visible) return;
            const target = progress(s.el);
            if (reducedMotion()) s.current = target;
            else {
                s.current += (target - s.current) * ease;
                if (Math.abs(target - s.current) < 0.002) s.current = target;
            }
            paint(s);
            if (s.current !== target) moving = true;
        });
        running = moving;
        if (moving) requestAnimationFrame(tick);
    };

    const kick = () => {
        if (!running) {
            running = true;
            requestAnimationFrame(tick);
        }
    };

    const io = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            const s = state.find((x) => x.el === entry.target);
            if (s) s.visible = entry.isIntersecting;
        });
        kick();
    }, { rootMargin: '20% 0px' });
    sections.forEach((el) => io.observe(el));

    window.addEventListener('scroll', kick, { passive: true });
    window.addEventListener('resize', kick, { passive: true });
}

