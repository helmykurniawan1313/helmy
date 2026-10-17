@php
    $sections = ['about', 'services', 'experience', 'skills', 'projects', 'education', 'contact'];
    $base = request()->routeIs('home', 'home.id') ? '' : route($locales[$locale]['route']);
@endphp
<header class="island-wrap">
    <nav class="island" aria-label="{{ $p['nav']['main'] }}">
        <a class="island-brand" href="{{ route($locales[$locale]['route']) }}">Helmy<span>.</span></a>

        <ul class="island-links">
            @foreach ($sections as $section)
                <li><a class="nav-link" href="{{ $base }}#{{ $section }}">{{ $p['nav'][$section] }}</a></li>
            @endforeach
        </ul>

        <div class="island-actions">
            <div class="lang-toggle" role="group" aria-label="{{ $p['nav']['language'] }}">
                @foreach ($locales as $code => $l)
                    <a href="{{ route($l['route']) }}" hreflang="{{ $code }}" lang="{{ $code }}"
                       title="{{ $l['name'] }}"
                       data-lang-link
                       @class(['active' => $code === $locale])
                       @if ($code === $locale) aria-current="true" @endif>{{ $l['label'] }}</a>
                @endforeach
            </div>
            <button type="button" class="appearance" data-theme-flip aria-label="{{ $p['nav']['theme'] }}">
                <span class="appearance-disc" aria-hidden="true"></span>
            </button>
            <button type="button" class="burger" data-menu-toggle aria-controls="menuOverlay" aria-expanded="false" aria-label="{{ $p['nav']['menu'] }}">
                <span></span><span></span>
            </button>
        </div>
    </nav>
</header>

{{-- Full-screen mobile menu --}}
<div class="menu-overlay" id="menuOverlay" hidden>
    <ul class="menu-links">
        @foreach ($sections as $i => $section)
            <li style="--i: {{ $i }}">
                <a href="{{ $base }}#{{ $section }}" data-menu-link>
                    <span class="menu-index">0{{ $i + 1 }}</span>{{ $p['nav'][$section] }}
                </a>
            </li>
        @endforeach
    </ul>
</div>
