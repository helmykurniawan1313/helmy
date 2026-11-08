// Admin UI: section editor (repeatable items, image previews, unsaved-changes state),
// the dark-mode button, and Ctrl/Cmd+S to save.

let counter = 0;

function refreshTitle(item) {
    const read = (name) => (name ? item.querySelector(`[data-field="${name}"]`)?.value?.trim() || '' : '');
    const title = item.querySelector('.repeat-title');
    const subtitle = item.querySelector('.repeat-subtitle');
    if (title) title.textContent = read(item.dataset.titleField) || title.dataset.empty;
    if (subtitle) subtitle.textContent = read(item.dataset.subtitleField);
}

function setCollapsed(item, collapsed) {
    item.classList.toggle('is-collapsed', collapsed);
    item.querySelector('.repeat-head')?.setAttribute('aria-expanded', String(!collapsed));
}

function renumber(list) {
    const items = list.querySelectorAll(':scope > .repeat-item');
    items.forEach((item, i) => {
        const num = item.querySelector('.repeat-num');
        if (num) num.textContent = i + 1;
    });
    const card = list.closest('.card');
    const count = card?.querySelector('[data-repeat-count]');
    if (count) count.textContent = items.length;
    const empty = card?.querySelector('[data-repeat-empty]');
    if (empty) empty.hidden = items.length > 0;
}

function initTheme() {
    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => btn.addEventListener('click', () => {
        const next = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-bs-theme', next);
        try { localStorage.setItem('theme', next); } catch (e) { /* private mode */ }
    }));
}

function initForm(form) {
    let dirty = false;
    const savedState = form.querySelector('[data-save-state]');
    const dirtyState = form.querySelector('[data-dirty-state]');

    const markDirty = () => {
        if (dirty) return;
        dirty = true;
        if (savedState) savedState.hidden = true;
        if (dirtyState) dirtyState.hidden = false;
    };

    form.addEventListener('input', markDirty);
    form.addEventListener('change', markDirty);
    form.addEventListener('submit', () => {
        dirty = false;
        const btn = form.querySelector('.save-bar button[type="submit"]');
        if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving…'; }
    });
    window.addEventListener('beforeunload', (e) => {
        if (dirty) { e.preventDefault(); e.returnValue = ''; }
    });

    // Ctrl/Cmd + S saves
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
            e.preventDefault();
            form.requestSubmit();
        }
    });

    const list = form.querySelector('[data-repeat-list]');
    const template = document.getElementById('repeat-template');

    if (list && template) {
        list.querySelectorAll('.repeat-item').forEach(refreshTitle);
        renumber(list);

        form.querySelector('[data-repeat-add]')?.addEventListener('click', () => {
            list.querySelectorAll('.repeat-item').forEach((it) => setCollapsed(it, true));
            list.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__ID__', `new${Date.now()}${counter++}`));
            const item = list.lastElementChild;
            refreshTitle(item);
            renumber(list);
            markDirty();
            item.scrollIntoView({ behavior: 'smooth', block: 'center' });
            item.querySelector('input:not([type=hidden]), textarea, select')?.focus({ preventScroll: true });
        });

        list.addEventListener('click', (e) => {
            const item = e.target.closest('.repeat-item');
            if (!item) return;
            const btn = e.target.closest('[data-action]');

            if (!btn) {
                if (e.target.closest('.repeat-head')) setCollapsed(item, !item.classList.contains('is-collapsed'));
                return;
            }

            const action = btn.dataset.action;
            if (action === 'up' && item.previousElementSibling) item.parentNode.insertBefore(item, item.previousElementSibling);
            if (action === 'down' && item.nextElementSibling) item.parentNode.insertBefore(item.nextElementSibling, item);
            if (action === 'remove') {
                const name = item.querySelector('.repeat-title')?.textContent || 'this item';
                if (!confirm(`Remove "${name}"? It is deleted when you save.`)) return;
                item.remove();
            }
            if (action !== 'remove') btn.focus();
            renumber(list);
            markDirty();
        });

        list.addEventListener('keydown', (e) => {
            const head = e.target.closest('.repeat-head');
            if (head && e.target === head && (e.key === 'Enter' || e.key === ' ')) {
                e.preventDefault();
                const item = head.closest('.repeat-item');
                setCollapsed(item, !item.classList.contains('is-collapsed'));
            }
        });

        list.addEventListener('input', (e) => {
            const item = e.target.closest('.repeat-item');
            if (item) refreshTitle(item);
        });

        form.querySelectorAll('[data-collapse-all]').forEach((btn) => btn.addEventListener('click', () => {
            const collapse = btn.dataset.collapseAll === 'true';
            list.querySelectorAll('.repeat-item').forEach((item) => setCollapsed(item, collapse));
        }));

        // Open any item that holds a validation error
        list.querySelectorAll('.is-invalid').forEach((el) => setCollapsed(el.closest('.repeat-item'), false));
    }

    // AI settings: dim a provider card when it's switched off
    form.addEventListener('change', (e) => {
        const select = e.target.closest('[data-priority]');
        if (select) select.closest('.provider-card')?.classList.toggle('is-off', select.value === '0');
    });

    // Preview a chosen image before saving
    form.addEventListener('change', (e) => {
        const input = e.target.closest('input[type="file"][data-preview]');
        if (!input || !input.files?.[0]) return;
        const img = input.closest('.image-field')?.querySelector('img');
        if (img) { img.src = URL.createObjectURL(input.files[0]); img.hidden = false; }
    });

    // Clearing an image empties its stored path
    form.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-image-clear]');
        if (!btn) return;
        const field = btn.closest('.image-field');
        field.querySelector('input[type="hidden"]').value = '';
        field.querySelector('input[type="file"]').value = '';
        const img = field.querySelector('img');
        if (img) img.hidden = true;
        markDirty();
    });

    return { clean: () => { dirty = false; } };
}

export function initEditor() {
    initTheme();

    const form = document.querySelector('[data-section-form]');
    const state = form ? initForm(form) : null;

    document.querySelectorAll('[data-confirm]').forEach((el) => el.addEventListener('submit', (e) => {
        if (!confirm(el.dataset.confirm)) e.preventDefault();
        else state?.clean();
    }));
}
