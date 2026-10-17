import '@fontsource-variable/geist';
import '@phosphor-icons/web/light';
import { initAssistant } from './assistant';
import { initReveal, initNumbers, initGlobe, initPhotoBanner, initScrollBg, initScrollExpand } from './enhance';

const THEME_KEY = 'theme';
const systemDark = matchMedia('(prefers-color-scheme: dark)');

const readPref = () => {
    try { return localStorage.getItem(THEME_KEY) || 'auto'; } catch (e) { return 'auto'; }
};

// pref: 'auto' (follow the OS) | 'light' | 'dark'
function applyTheme(pref, animate = false) {
    const root = document.documentElement;
    const theme = pref === 'auto' ? (systemDark.matches ? 'dark' : 'light') : pref;

    // Ease the brightness change rather than flashing between themes
    if (animate && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
        root.classList.add('theme-transition');
        setTimeout(() => root.classList.remove('theme-transition'), 400);
    }
    root.setAttribute('data-bs-theme', theme);

    document.querySelectorAll('[data-theme-choice]').forEach((btn) => {
        btn.setAttribute('aria-checked', String(btn.dataset.themeChoice === pref));
    });
}

function setPref(pref) {
    try {
        if (pref === 'auto') localStorage.removeItem(THEME_KEY);
        else localStorage.setItem(THEME_KEY, pref);
    } catch (e) { /* storage unavailable */ }
    applyTheme(pref, true);
}

// Live-follow the OS while on Auto
systemDark.addEventListener('change', () => {
    if (readPref() === 'auto') applyTheme('auto', true);
});

document.addEventListener('DOMContentLoaded', () => {
    applyTheme(readPref());
    document.querySelectorAll('[data-theme-choice]').forEach((btn) => {
        btn.addEventListener('click', () => setPref(btn.dataset.themeChoice));
    });
    // Navbar appearance switch: flips the current look (footer keeps Auto)
    document.querySelectorAll('[data-theme-flip]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-bs-theme');
            setPref(current === 'dark' ? 'light' : 'dark');
        });
    });

    // Island nav condenses once the page scrolls (observer, not a scroll listener)
    const island = document.querySelector('.island');
    const sentinel = document.createElement('div');
    sentinel.style.cssText = 'position:absolute;top:0;height:40px;width:1px;pointer-events:none';
    document.body.prepend(sentinel);
    new IntersectionObserver(([entry]) => island?.classList.toggle('scrolled', !entry.isIntersecting))
        .observe(sentinel);

    initMenu();

    // Highlight active section in navbar
    const links = [...document.querySelectorAll('.island .nav-link[href^="#"]')];
    const sections = links.map((l) => document.querySelector(l.getAttribute('href'))).filter(Boolean);
    const spy = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                links.forEach((l) => l.classList.toggle('active', l.getAttribute('href') === `#${entry.target.id}`));
            }
        });
    }, { rootMargin: '-45% 0px -50% 0px' });
    sections.forEach((s) => spy.observe(s));

    initReveal();
    initNumbers();
    initGlobe();
    initPhotoBanner();
    initScrollBg();
    initScrollExpand();
    initAssistant();
});

// Keep the current section when switching language
document.addEventListener('click', (e) => {
    const link = e.target.closest('[data-lang-link]');
    if (link && window.location.hash) {
        e.preventDefault();
        window.location.href = link.href + window.location.hash;
    }
});

// Full-screen menu: burger morphs to X, links stagger up from a mask
function initMenu() {
    const toggle = document.querySelector('[data-menu-toggle]');
    const overlay = document.getElementById('menuOverlay');
    if (!toggle || !overlay) return;

    const setOpen = (open) => {
        toggle.setAttribute('aria-expanded', String(open));
        document.documentElement.classList.toggle('menu-open', open);
        if (open) {
            overlay.hidden = false;
            requestAnimationFrame(() => overlay.classList.add('open'));
            overlay.querySelector('a')?.focus({ preventScroll: true });
        } else {
            overlay.classList.remove('open');
            overlay.addEventListener('transitionend', () => {
                if (!overlay.classList.contains('open')) overlay.hidden = true;
            }, { once: true });
        }
    };

    toggle.addEventListener('click', () => setOpen(toggle.getAttribute('aria-expanded') !== 'true'));
    overlay.querySelectorAll('[data-menu-link]').forEach((a) => a.addEventListener('click', () => setOpen(false)));
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
            setOpen(false);
            toggle.focus();
        }
    });
}
