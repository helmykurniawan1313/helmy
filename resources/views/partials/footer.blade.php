@php
    $ui = $p['ui'];
    $base = request()->routeIs('home', 'home.id') ? '' : route($p['locales'][app()->getLocale()]['route'] ?? 'home');
    $footerLinks = array_values(array_filter(['about', 'services', 'experience', 'skills', 'education', empty($p['publications']) ? null : 'publications', 'projects', 'contact']));
@endphp
<footer class="pf-footer">
    <div class="container">
        <div class="footer-top">
            <div class="footer-brand">
                <a class="footer-logo" href="{{ $base }}#top">Helmy<span>.</span></a>
                <p class="footer-blurb">{{ $p['role'] }} &middot; {{ $p['location'] }}</p>
                <p class="footer-note">{{ $ui['footer_blurb'] }}</p>
                <div class="footer-actions">
                    <a href="mailto:{{ $p['email'] }}" class="island-btn island-btn-primary">
                        {{ $ui['cta_contact'] }} <span class="island-btn-icon"><i class="ph-light ph-arrow-up-right"></i></span>
                    </a>
                    @if (\App\Support\Resume::available())
                        <a href="{{ route('resume') }}" class="island-btn island-btn-outline" download>
                            {{ $ui['download_cv'] }} <span class="island-btn-icon"><i class="ph-light ph-download-simple"></i></span>
                        </a>
                    @endif
                </div>
            </div>

            <nav class="footer-col footer-explore" aria-label="{{ $ui['footer_explore'] }}">
                <h2 class="footer-head">{{ $ui['footer_explore'] }}</h2>
                <ul>
                    @foreach ($footerLinks as $section)
                        <li><a href="{{ $base }}#{{ $section }}">{{ $p['nav'][$section] }}</a></li>
                    @endforeach
                </ul>
            </nav>

            <div class="footer-col">
                <h2 class="footer-head">{{ $ui['footer_contact'] }}</h2>
                <ul>
                    <li><a href="mailto:{{ $p['email'] }}"><i class="ph-light ph-envelope-simple"></i> {{ $p['email'] }}</a></li>
                    <li><a href="https://wa.me/{{ $p['whatsapp'] }}" target="_blank" rel="noopener"><i class="ph-light ph-whatsapp-logo"></i> {{ $p['phone'] }}</a></li>
                    @include('partials.socials', ['variant' => 'list'])
                    <li><span><i class="ph-light ph-map-pin"></i> {{ $p['location'] }}</span></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p class="mb-0">&copy; {{ date('Y') }} {{ $p['name'] }}. {{ $ui['rights'] }}</p>

            {{-- Theme follows the OS by default; this is a quiet override, not a sun/moon switch --}}
            <div class="theme-choice" role="radiogroup" aria-label="{{ $ui['theme_label'] }}">
                <span class="theme-choice-label">{{ $ui['theme_label'] }}</span>
                @foreach (['auto', 'light', 'dark'] as $mode)
                    <button type="button" role="radio" aria-checked="false" data-theme-choice="{{ $mode }}">{{ $ui['theme_'.$mode] }}</button>
                @endforeach
            </div>

            <a href="#top" class="footer-top-link">{{ $ui['back_to_top'] }} <i class="ph-light ph-arrow-up"></i></a>
        </div>
    </div>
</footer>
