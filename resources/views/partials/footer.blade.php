<footer class="pf-footer">
    <div class="container footer-row">
        <p class="mb-0">&copy; {{ date('Y') }} {{ $p['name'] }}. {{ $p['ui']['rights'] }}</p>

        {{-- Theme follows the OS by default; this is a quiet override, not a sun/moon switch --}}
        <div class="theme-choice" role="radiogroup" aria-label="{{ $p['ui']['theme_label'] }}">
            <span class="theme-choice-label">{{ $p['ui']['theme_label'] }}</span>
            @foreach (['auto', 'light', 'dark'] as $mode)
                <button type="button" role="radio" aria-checked="false" data-theme-choice="{{ $mode }}">{{ $p['ui']['theme_'.$mode] }}</button>
            @endforeach
        </div>

        <p class="mb-0 small">{{ $p['role'] }} &middot; {{ $p['location'] }}</p>
    </div>
</footer>
