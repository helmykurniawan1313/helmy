// Portfolio: type filter + a viewer that plays videos, enlarges photos and shows project details.
const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

export function initWork() {
    const grid = document.querySelector('[data-work-grid]');
    if (!grid) return;

    const bar = document.querySelector('.work-filter');
    const empty = document.querySelector('[data-work-empty]');
    const reduced = () => matchMedia('(prefers-reduced-motion: reduce)').matches;
    const ease = 'cubic-bezier(0.22, 1, 0.36, 1)';

    // Sliding pill behind the active tab
    const pill = bar ? Object.assign(document.createElement('span'), { className: 'work-pill' }) : null;
    if (pill) { pill.setAttribute('aria-hidden', 'true'); bar.prepend(pill); bar.classList.add('has-pill'); }
    const movePill = (btn, animate = true) => {
        if (!pill || !btn) return;
        pill.style.transition = animate && !reduced() ? '' : 'none';
        pill.style.width = `${btn.offsetWidth}px`;
        pill.style.height = `${btn.offsetHeight}px`;
        pill.style.transform = `translate(${btn.offsetLeft}px, ${btn.offsetTop}px)`;
        if (btn.offsetLeft < bar.scrollLeft || btn.offsetLeft + btn.offsetWidth > bar.scrollLeft + bar.clientWidth) {
            bar.scrollTo({ left: btn.offsetLeft - 16, behavior: reduced() ? 'auto' : 'smooth' });
        }
    };
    const active = () => bar?.querySelector('.is-active');
    requestAnimationFrame(() => movePill(active(), false));
    addEventListener('resize', () => movePill(active(), false));
    document.fonts?.ready.then(() => movePill(active(), false));

    // Show 6 per tab; "View more" reveals the rest of the current tab
    const LIMIT = 6;
    const more = document.querySelector('[data-work-more]');
    const moreBtn = more?.querySelector('button');
    const ofType = (f) => [...grid.querySelectorAll('.work-card')].filter((c) => c.dataset.type === f);
    const updateMore = (f) => {
        if (!more) return;
        const extra = ofType(f).length - LIMIT;
        more.hidden = extra <= 0;
        if (moreBtn && extra > 0) {
            moreBtn.querySelector('[data-more-count]').textContent = extra;
            moreBtn.setAttribute('aria-expanded', 'false');
        }
    };
    moreBtn?.addEventListener('click', () => {
        const f = active()?.dataset.filter;
        const rest = ofType(f).filter((c) => c.hidden);
        rest.forEach((c, i) => {
            c.hidden = false;
            c.style.opacity = '1';
            c.style.transform = 'none';
            if (!reduced()) {
                c.animate(
                    [{ opacity: 0, transform: 'translateY(18px) scale(0.98)' }, { opacity: 1, transform: 'none' }],
                    { duration: 520, delay: Math.min(i, 8) * 60, easing: ease, fill: 'backwards' },
                );
            }
        });
        moreBtn.setAttribute('aria-expanded', 'true');
        more.hidden = true;
        rest[0]?.querySelector('.work-open')?.focus({ preventScroll: true });
    });
    updateMore(active()?.dataset.filter);

    let busy = null;
    document.querySelectorAll('[data-filter]').forEach((btn) => btn.addEventListener('click', async () => {
        if (btn.classList.contains('is-active')) return;
        const f = btn.dataset.filter;
        document.querySelectorAll('[data-filter]').forEach((b) => {
            b.classList.toggle('is-active', b === btn);
            b.setAttribute('aria-selected', String(b === btn));
        });
        movePill(btn);
        grid.setAttribute('aria-labelledby', btn.id);

        const token = (busy = {});
        const cards = [...grid.querySelectorAll('.work-card')];
        const outgoing = [...cards.filter((c) => !c.hidden), ...(empty && !empty.hidden ? [empty] : [])];
        const all = cards.filter((c) => c.dataset.type === f);
        const incoming = all.slice(0, LIMIT);

        // 1) fade the current items out
        if (!reduced() && outgoing.length) {
            await Promise.all(outgoing.map((el) => el.animate(
                [{ opacity: 1, transform: 'none' }, { opacity: 0, transform: 'translateY(8px) scale(0.98)' }],
                { duration: 160, easing: 'ease-in', fill: 'forwards' },
            ).finished.catch(() => {})));
        }
        if (token !== busy) return; // another tab was clicked meanwhile

        // 2) swap
        cards.forEach((c) => {
            c.getAnimations().forEach((a) => a.cancel());
            c.hidden = !incoming.includes(c);
            c.style.opacity = '1';
            c.style.transform = 'none';
        });
        if (empty) { empty.getAnimations().forEach((a) => a.cancel()); empty.hidden = incoming.length > 0; }
        updateMore(f);

        // 3) bring the new ones in, staggered
        if (reduced()) return;
        const enter = incoming.length ? incoming : (empty ? [empty] : []);
        enter.forEach((el, i) => el.animate(
            [{ opacity: 0, transform: 'translateY(18px) scale(0.98)' }, { opacity: 1, transform: 'none' }],
            { duration: 520, delay: Math.min(i, 8) * 60, easing: ease, fill: 'backwards' },
        ));
    }));

    // Arrow keys move between tabs
    document.querySelector('.work-filter')?.addEventListener('keydown', (e) => {
        if (!['ArrowLeft', 'ArrowRight'].includes(e.key)) return;
        const tabs = [...document.querySelectorAll('[data-filter]')];
        const i = tabs.indexOf(document.activeElement);
        if (i < 0) return;
        const next = tabs[(i + (e.key === 'ArrowRight' ? 1 : tabs.length - 1)) % tabs.length];
        next.focus();
        next.click();
    });

    let data = [];
    try { data = JSON.parse(document.getElementById('work-data')?.textContent || '[]'); } catch (e) { /* ignore */ }
    const viewer = document.querySelector('[data-work-viewer]');
    const stage = viewer?.querySelector('[data-work-stage]');
    const caption = viewer?.querySelector('[data-work-caption]');
    if (!viewer || !stage) return;

    const close = () => { stage.innerHTML = ''; if (viewer.open) viewer.close(); };

    grid.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-work]');
        const w = btn && data[btn.dataset.work];
        if (!w) return;

        if (w.type === 'video' && w.embed?.kind === 'iframe') {
            stage.innerHTML = `<iframe src="${esc(w.embed.src)}" title="${esc(w.title)}" allow="autoplay; encrypted-media; picture-in-picture; fullscreen" allowfullscreen></iframe>`;
        } else if (w.type === 'video' && w.embed) {
            stage.innerHTML = `<video src="${esc(w.embed.src)}" controls autoplay playsinline${w.src ? ` poster="${esc(w.src)}"` : ''}></video>`;
        } else if (w.src) {
            stage.innerHTML = `<img src="${esc(w.src)}" alt="${esc(w.title)}">`;
        } else {
            stage.innerHTML = '';
        }
        stage.dataset.kind = w.type === 'video' && w.embed ? 'video' : 'image';

        caption.innerHTML = `<h3 class="h5 fw-bold mb-1">${esc(w.title)}</h3>`
            + (w.client ? `<div class="text-body-secondary mb-2">${esc(w.client)}</div>` : '')
            + (w.description ? `<p class="mb-2">${esc(w.description)}</p>` : '')
            + (w.tech.length ? `<div class="work-tech mb-2">${w.tech.map((t) => `<span>${esc(t)}</span>`).join('')}</div>` : '')
            + (w.link ? `<a href="${esc(w.link)}" target="_blank" rel="noopener" class="link-arrow">${esc(viewer.dataset.visit || 'Visit site')} ↗</a>` : '');

        viewer.showModal();
    });

    viewer.querySelector('[data-work-close]')?.addEventListener('click', close);
    viewer.addEventListener('click', (e) => { if (e.target === viewer) close(); });
    viewer.addEventListener('close', () => { stage.innerHTML = ''; });
}
