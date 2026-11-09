{{--
    Social links from Admin > Personal info. Only filled ones are shown.
    Usage: @include('partials.socials', ['variant' => 'pills' | 'list'])
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
        ! empty($p['instagram_business']) ? [
            'url' => $p['instagram_business'], 'icon' => 'ph-storefront', 'label' => ($p['instagram_business_name'] ?? '') ?: ($handle($p['instagram_business']) ?? 'Instagram'),
            'sub' => $ui['social_business'] ?? 'Business', 'kind' => 'business',
        ] : null,
    ]));
@endphp

@if ($socials)
    @if (($variant ?? 'list') === 'pills')
        <div class="social-pills" aria-label="{{ $ui['social_title'] ?? 'Social media' }}">
            @foreach ($socials as $s)
                <a href="{{ $s['url'] }}" class="social-pill is-{{ $s['kind'] }}" target="_blank" rel="noopener me">
                    <i class="ph-light {{ $s['icon'] }}"></i>
                    <span class="social-pill-text"><strong>{{ $s['label'] }}</strong>@if ($s['sub'])<small>{{ $s['sub'] }}</small>@endif</span>
                </a>
            @endforeach
        </div>
    @else
        @foreach ($socials as $s)
            <li><a href="{{ $s['url'] }}" target="_blank" rel="noopener me"><i class="ph-light {{ $s['icon'] }}"></i> {{ $s['label'] }}@if ($s['sub'])<span class="footer-sub">{{ $s['sub'] }}</span>@endif</a></li>
        @endforeach
    @endif
@endif
