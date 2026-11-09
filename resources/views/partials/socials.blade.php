{{--
    Personal social links from Admin > Personal info (LinkedIn, Instagram).
    Only filled ones are shown. The business account you manage lives on its
    Experience card instead (Admin > Career > Experience > "Business account I manage").
    Usage: @include('partials.socials', ['variant' => 'about' | 'list'])
--}}
@php
    $handle = function (?string $url) {
        $path = trim((string) parse_url((string) $url, PHP_URL_PATH), '/');
        $last = $path === '' ? '' : basename($path);
        return $last !== '' && ! in_array($last, ['in', 'company'], true) ? '@'.$last : null;
    };
    $ui = $p['ui'];
    $socials = array_values(array_filter([
        ! empty($p['linkedin']) ? [
            'url' => $p['linkedin'], 'icon' => 'ph-linkedin-logo', 'label' => $ui['social_linkedin'] ?? 'LinkedIn', 'sub' => null, 'kind' => 'linkedin',
        ] : null,
        ! empty($p['instagram']) ? [
            'url' => $p['instagram'], 'icon' => 'ph-instagram-logo', 'label' => $ui['social_instagram'] ?? 'Instagram', 'sub' => $handle($p['instagram']), 'kind' => 'instagram',
        ] : null,
    ]));
@endphp

@if ($socials)
    @if (($variant ?? 'list') === 'about')
        <div class="about-socials">
            <span class="about-socials-label">{{ $ui['social_find'] ?? 'Find me on' }}</span>
            <div class="about-socials-row">
                @foreach ($socials as $s)
                    <a href="{{ $s['url'] }}" class="social-chip is-{{ $s['kind'] }}" target="_blank" rel="noopener me">
                        <i class="ph-light {{ $s['icon'] }}"></i>
                        <span><strong>{{ $s['label'] }}</strong></span>
                    </a>
                @endforeach
            </div>
        </div>
    @else
        @foreach ($socials as $s)
            <li><a href="{{ $s['url'] }}" target="_blank" rel="noopener me"><i class="ph-light {{ $s['icon'] }}"></i> {{ $s['label'] }}@if ($s['sub'])<span class="footer-sub">{{ $s['sub'] }}</span>@endif</a></li>
        @endforeach
    @endif
@endif
