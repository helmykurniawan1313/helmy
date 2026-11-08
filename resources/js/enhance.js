import { animate, inView, stagger } from 'motion';
import NumberFlow, { define } from 'number-flow';

/**
 * Stable viewport height for scroll effects. On phones the address bar
 * shows/hides while scrolling, which changes innerHeight and made scroll-tied
 * effects jump. This value only updates when the WIDTH changes (rotation,
 * window resize), so it behaves like CSS svh.
 */
let stableVh = typeof window !== 'undefined' ? window.innerHeight : 800;
let stableVw = typeof window !== 'undefined' ? window.innerWidth : 0;
if (typeof window !== 'undefined') {
    window.addEventListener('resize', () => {
        if (window.innerWidth !== stableVw) {
            stableVw = window.innerWidth;
            stableVh = window.innerHeight;
        }
    });
}
const viewH = () => stableVh;

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
    document.querySelectorAll('.photo-banner, [data-about-facts]').forEach((el) => {
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
        const vh = viewH();
        const startAt = 0.55;
        const raw = (vh * startAt - el.getBoundingClientRect().top) / (vh * startAt);
        return Math.min(1, Math.max(0, raw));
    };

    const paint = (s) => {
        const color = mix(s.from, s.to, s.current);
        if (color !== s.painted) {
            if (!s.el.hasAttribute('data-scroll-fill')) s.el.style.backgroundColor = color;
            s.el.style.setProperty('--scroll-p', s.current.toFixed(3));
            s.el.classList.toggle('is-filled', s.current > 0.5);
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
        const vh = viewH();
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


/**
 * A card that grows to fill its section on scroll. The blue layer starts at the
 * card's rectangle (measured here) and expands to the section edges; CSS animates
 * it with clip-path using --scroll-p from initScrollBg.
 */
export function initScrollFill() {
    document.querySelectorAll('[data-scroll-fill]').forEach((section) => {
        const card = section.querySelector('[data-fill-from]');
        if (!card) return;
        const measure = () => {
            const s = section.getBoundingClientRect();
            const c = card.getBoundingClientRect();
            section.style.setProperty('--fill-t', `${Math.max(0, c.top - s.top)}px`);
            section.style.setProperty('--fill-r', `${Math.max(0, s.right - c.right)}px`);
            section.style.setProperty('--fill-b', `${Math.max(0, s.bottom - c.bottom)}px`);
            section.style.setProperty('--fill-l', `${Math.max(0, c.left - s.left)}px`);
        };
        measure();
        addEventListener('resize', measure);
        addEventListener('load', measure);
        document.fonts?.ready.then(measure);
        new ResizeObserver(measure).observe(section);
    });
}

/**
 * Experience timeline: the line beside the roles fills as you scroll through a
 * company (--xp-p), and each role's dot lights up when its role reaches the
 * reading line.
 */
export function initXpTimeline() {
    const tracks = [...document.querySelectorAll('[data-xp-track]')];
    if (!tracks.length) return;
    const reduced = matchMedia('(prefers-reduced-motion: reduce)');
    let ticking = false;

    const update = () => {
        ticking = false;
        const line = viewH() * 0.6;
        tracks.forEach((track) => {
            const r = track.getBoundingClientRect();
            if (r.bottom < -200 || r.top > viewH() + 200) return;
            const p = reduced.matches ? 1 : Math.min(1, Math.max(0, (line - r.top) / Math.max(1, r.height)));
            track.style.setProperty('--xp-p', p.toFixed(3));
            track.querySelectorAll('.xp-role').forEach((role) => {
                role.classList.toggle('is-lit', reduced.matches || role.getBoundingClientRect().top < line);
            });
        });
    };

    const onScroll = () => { if (!ticking) { ticking = true; requestAnimationFrame(update); } };
    addEventListener('scroll', onScroll, { passive: true });
    addEventListener('resize', onScroll);
    update();
}

/**
 * Services spotlight: the service crossing the reading line becomes active
 * (others dim); a counter + bar in the sticky intro follows along. Hover also
 * spotlights on pointer devices.
 */
export function initSvcSpotlight() {
    const list = document.querySelector('[data-svc-spotlight]');
    if (!list) return;
    const items = [...list.querySelectorAll('.svc-item')];
    const section = list.closest('section') || list;
    const now = document.querySelector('[data-svc-now]');
    const bar = document.querySelector('[data-svc-bar]');
    const title = document.querySelector('[data-svc-title]');
    let active = -1;
    let hovered = -1;
    let ticking = false;
    let lastExpand = -1;

    const set = (i) => {
        if (i === active) return;
        active = i;
        list.classList.toggle('has-active', i >= 0);
        items.forEach((el, n) => el.classList.toggle('is-active', n === i));
        const shown = Math.max(0, i);
        if (now) now.textContent = String(shown + 1).padStart(2, '0');
        if (bar) bar.style.transform = `scaleX(${(shown + 1) / items.length})`;
        if (title) title.textContent = items[shown]?.querySelector('h3')?.textContent ?? '';
    };

    const fromScroll = () => {
        ticking = false;
        if (hovered >= 0) return;
        const r = list.getBoundingClientRect();
        if (r.bottom < 0 || r.top > viewH()) { set(-1); return; }
        // Wait until the blue panel has fully expanded before spotlighting
        const expand = parseFloat(getComputedStyle(section).getPropertyValue('--p')) || 0;
        if (section.hasAttribute('data-scroll-expand') && expand < 0.98) {
            set(-1);
            if (expand !== lastExpand) { lastExpand = expand; requestAnimationFrame(fromScroll); }
            return;
        }
        lastExpand = expand;
        const line = viewH() * 0.5;
        let idx = 0;
        items.forEach((el, n) => { if (el.getBoundingClientRect().top <= line) idx = n; });
        set(idx);
    };

    addEventListener('scroll', () => { if (!ticking) { ticking = true; requestAnimationFrame(fromScroll); } }, { passive: true });
    addEventListener('resize', fromScroll);

    if (matchMedia('(hover: hover) and (pointer: fine)').matches) {
        items.forEach((el, n) => {
            el.addEventListener('mouseenter', () => { hovered = n; active = -2; set(n); });
            el.addEventListener('mouseleave', () => { hovered = -1; fromScroll(); });
        });
    }
    fromScroll();
}
