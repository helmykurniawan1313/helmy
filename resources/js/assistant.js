// Floating "Ask AI" chat. Talks to POST /assistant (server tries Gemini, then Groq).

const escapeHtml = (s) => s.replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

// Tiny, safe formatter: escape first, then allow **bold**, "- " bullets and links to email/wa.me.
function format(text) {
    const lines = escapeHtml(text).split('\n');
    let html = '';
    let inList = false;
    for (const raw of lines) {
        const line = raw.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
        const bullet = line.match(/^\s*(?:[-•*])\s+(.*)$/);
        if (bullet) {
            if (!inList) { html += '<ul>'; inList = true; }
            html += `<li>${bullet[1]}</li>`;
            continue;
        }
        if (inList) { html += '</ul>'; inList = false; }
        if (line.trim()) html += `<p>${line}</p>`;
    }
    if (inList) html += '</ul>';
    return html
        .replace(/([\w.+-]+@[\w-]+\.[\w.]+)/g, '<a href="mailto:$1">$1</a>')
        .replace(/(https?:\/\/[^\s<]+)/g, '<a href="$1" target="_blank" rel="noopener">$1</a>');
}

export function initAssistant() {
    const root = document.querySelector('[data-assistant]');
    if (!root) return;

    const panel = root.querySelector('.ai-panel');
    const toggle = root.querySelector('[data-assistant-toggle]');
    const log = root.querySelector('[data-assistant-log]');
    const form = root.querySelector('[data-assistant-form]');
    const input = form.querySelector('textarea');
    const send = form.querySelector('.ai-send');
    const suggest = root.querySelector('[data-assistant-suggest]');
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    const history = [];
    let busy = false;

    const setOpen = (open) => {
        panel.hidden = !open;
        root.classList.toggle('is-open', open);
        toggle.setAttribute('aria-expanded', String(open));
        if (open) setTimeout(() => input.focus(), 50);
        else toggle.focus();
    };

    const scrollDown = () => { log.scrollTop = log.scrollHeight; };

    const addMessage = (role, html) => {
        const el = document.createElement('div');
        el.className = `ai-msg ai-msg-${role === 'user' ? 'user' : 'bot'}`;
        el.innerHTML = html;
        log.appendChild(el);
        scrollDown();
        return el;
    };

    const autosize = () => {
        input.style.height = 'auto';
        input.style.height = `${Math.min(input.scrollHeight, 120)}px`;
    };

    async function ask(question) {
        const text = question.trim();
        if (!text || busy) return;
        busy = true;
        send.disabled = true;
        suggest?.remove();

        addMessage('user', `<p>${escapeHtml(text)}</p>`);
        history.push({ role: 'user', content: text });
        input.value = '';
        autosize();

        const pending = addMessage('bot', `<p class="ai-typing"><span></span><span></span><span></span><span class="visually-hidden">${escapeHtml(root.dataset.thinking)}</span></p>`);

        try {
            const res = await fetch(root.dataset.endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({ locale: root.dataset.locale, messages: history.slice(-8) }),
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok || !data.reply) {
                throw new Error(res.status === 429 ? root.dataset.limit : (data.error || root.dataset.error));
            }
            pending.innerHTML = format(data.reply);
            history.push({ role: 'assistant', content: data.reply });
        } catch (err) {
            pending.classList.add('ai-msg-error');
            pending.innerHTML = `<p>${escapeHtml(err.message || root.dataset.error)}</p>`;
            history.pop(); // let the visitor retry the same question
        } finally {
            busy = false;
            send.disabled = false;
            scrollDown();
            input.focus();
        }
    }

    toggle.addEventListener('click', () => setOpen(panel.hidden));
    root.querySelector('[data-assistant-close]').addEventListener('click', () => setOpen(false));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !panel.hidden) setOpen(false); });

    form.addEventListener('submit', (e) => { e.preventDefault(); ask(input.value); });
    input.addEventListener('input', autosize);
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) { e.preventDefault(); ask(input.value); }
    });
    suggest?.addEventListener('click', (e) => {
        const chip = e.target.closest('.ai-chip');
        if (chip) ask(chip.textContent);
    });
}
