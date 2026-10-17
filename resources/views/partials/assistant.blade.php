@php $a = $p['assistant']; @endphp
<div class="ai-widget" data-assistant
     data-endpoint="{{ route('assistant') }}"
     data-locale="{{ app()->getLocale() }}"
     data-error="{{ $a['error'] }}"
     data-limit="{{ $a['limit'] }}"
     data-thinking="{{ $a['thinking'] }}">

    <section class="ai-panel" id="aiPanel" role="dialog" aria-modal="false" aria-labelledby="aiTitle" hidden>
        <header class="ai-head">
            <span class="ai-avatar" aria-hidden="true"><i class="ph-light ph-sparkle"></i></span>
            <div class="ai-head-text">
                <h2 id="aiTitle" class="ai-title">{{ $a['title'] }}</h2>
                <p class="ai-subtitle">{{ $a['subtitle'] }}</p>
            </div>
            <button type="button" class="ai-icon-btn" data-assistant-close aria-label="{{ $a['close'] }}">
                <i class="ph-light ph-x" aria-hidden="true"></i>
            </button>
        </header>

        <div class="ai-log" data-assistant-log aria-live="polite">
            <div class="ai-msg ai-msg-bot"><p>{{ $a['greeting'] }}</p></div>
            <div class="ai-suggest" data-assistant-suggest>
                @foreach ($a['suggestions'] as $s)
                    <button type="button" class="ai-chip">{{ $s }}</button>
                @endforeach
            </div>
        </div>

        <form class="ai-form" data-assistant-form>
            <label for="aiInput" class="visually-hidden">{{ $a['placeholder'] }}</label>
            <textarea id="aiInput" class="ai-input" rows="1" maxlength="1000" placeholder="{{ $a['placeholder'] }}" autocomplete="off" required></textarea>
            <button type="submit" class="ai-send" aria-label="{{ $a['send'] }}">
                <i class="ph-light ph-arrow-up" aria-hidden="true"></i>
            </button>
        </form>
        <p class="ai-disclaimer">{{ $a['disclaimer'] }}</p>
    </section>

    <button type="button" class="ai-fab" data-assistant-toggle aria-controls="aiPanel" aria-expanded="false" aria-label="{{ $a['open'] }}">
        <i class="ph-light ph-sparkle ai-fab-open" aria-hidden="true"></i>
        <i class="ph-light ph-x ai-fab-close" aria-hidden="true"></i>
        <span class="ai-fab-label">{{ $a['open'] }}</span>
    </button>
</div>
