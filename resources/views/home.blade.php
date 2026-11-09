@extends('layouts.app')

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Person',
    'name' => $p['name'],
    'jobTitle' => $p['role'],
    'url' => $p['website'],
    'image' => asset($p['photo']),
    'email' => 'mailto:'.$p['email'],
    'telephone' => $p['phone'],
    'description' => $p['about'],
    'address' => [
        '@type' => 'PostalAddress',
        'addressLocality' => 'Surabaya',
        'addressRegion' => 'Jawa Timur',
        'addressCountry' => 'ID',
    ],
    'alumniOf' => [
        ['@type' => 'CollegeOrUniversity', 'name' => 'Universitas Pembangunan Nasional Veteran Jawa Timur'],
    ],
    'worksFor' => ['@type' => 'Organization', 'name' => 'Porta Branding Agency'],
    'knowsAbout' => array_column($p['skills'], 'name'),
    'knowsLanguage' => ['id', 'en'],
    'hasCredential' => array_map(fn ($c) => [
        '@type' => 'EducationalOccupationalCredential',
        'name' => $c['title'],
        'recognizedBy' => ['@type' => 'Organization', 'name' => $c['issuer']],
    ], $p['certificates']),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
@endpush

@section('content')
@php
    $ui = $p['ui'];
    $b = $p['banner'];
    $years = now()->year - $p['career_start'];
    $current = $p['experiences'][0];
@endphp

{{-- ========== HERO BANNER ========== --}}
<section class="banner hero" id="top" aria-labelledby="hero-title">
    <span class="glow g1"></span>
    <span class="glow g2"></span>
    <div class="container">
        <h1 id="hero-title" class="display-xl mb-4 reveal">
            {{ $b['hero_title'] }}<br>
            <span class="text-accent">{{ $b['hero_title_accent'] }}</span>
        </h1>
        <p class="lead-xl mb-4 reveal">{{ $b['hero_sub'] }}</p>
        <div class="d-flex flex-wrap gap-3 justify-content-center align-items-center reveal">
            <a href="#contact" class="island-btn island-btn-primary">{{ $ui['cta_contact'] }} <span class="island-btn-icon"><i class="ph-light ph-arrow-up-right"></i></span></a>
            @if (\App\Support\Resume::available())
                <a href="{{ route('resume') }}" class="island-btn island-btn-outline" download>
                    {{ $ui['download_cv'] }} <span class="island-btn-icon"><i class="ph-light ph-download-simple"></i></span>
                </a>
            @endif
            <a href="#projects" class="link-arrow">{{ $ui['cta_projects'] }} <i class="ph-light ph-caret-right"></i></a>
        </div>


        <div class="scroll-hint" aria-hidden="true">
            {{ $b['scroll'] }}
            <i class="ph-light ph-caret-down"></i>
        </div>
    </div>
</section>

{{-- ========== BIG IMAGE ========== --}}
<section class="photo-banner" aria-labelledby="feature-title">
    @if (! empty($b['feature_image']))
        <img class="photo-banner-img" src="{{ asset($b['feature_image']) }}"
             sizes="100vw" width="2400" height="1350" loading="lazy" decoding="async"
             alt="{{ $b['feature_alt'] ?? '' }}">
    @else
        <img class="photo-banner-img"
             src="{{ asset('images/server-room-1200.jpg') }}"
             srcset="{{ asset('images/server-room-1200.jpg') }} 1200w, {{ asset('images/server-room-2400.jpg') }} 2400w"
             sizes="100vw" width="2400" height="1350" loading="lazy" decoding="async"
             alt="{{ $b['feature_alt'] ?? '' }}">
    @endif

    @php
        // Optional animation from the admin: Lottie (.json/.lottie), GIF/image, or video
        $fMedia = trim((string) ($b['feature_media'] ?? ''));
        $fPath = strtolower(parse_url($fMedia, PHP_URL_PATH) ?? '');
        $fKind = match (true) {
            $fMedia === '' => null,
            str_ends_with($fPath, '.json') || str_ends_with($fPath, '.lottie') || str_contains($fMedia, 'lottie.host') => 'lottie',
            (bool) preg_match('/\.(mp4|webm|mov)$/', $fPath) => 'video',
            default => 'image',
        };
        $fMode = ($b['feature_media_mode'] ?? 'overlay') === 'replace' ? 'replace' : 'overlay';
        $fFx = ($b['feature_fx'] ?? true) !== false && $fMode !== 'replace';
    @endphp

    @if ($fFx)
        <canvas class="photo-fx" data-photo-fx aria-hidden="true"></canvas>
    @endif

    @if ($fKind)
        <div @class(['photo-media', 'is-replace' => $fMode === 'replace']) aria-hidden="true">
            @if ($fKind === 'lottie')
                <dotlottie-wc data-lottie src="{{ $fMedia }}" autoplay loop></dotlottie-wc>
            @elseif ($fKind === 'video')
                <video src="{{ $fMedia }}" autoplay muted loop playsinline preload="metadata"></video>
            @else
                <img src="{{ $fMedia }}" alt="" loading="lazy" decoding="async">
            @endif
        </div>
    @endif

    <div class="photo-banner-copy">
        <h2 id="feature-title" class="display-lg">{{ $b['feature_title'] }}</h2>
    </div>
</section>

{{-- ========== 01 ABOUT ========== --}}
@php $about = $b['about_section']; @endphp
<section class="banner about-section" id="about" aria-labelledby="about-title">
    <div class="container">
        <div class="about-grid">
            <div class="about-intro reveal">
                <span class="eyebrow-pill">{{ $p['nav']['about'] }}</span>
                @php
                    // Highlight the last word of the title ("Helmy.") with the brand gradient
                    $aboutWords = preg_split('/\s+/', trim($about['title']));
                    $aboutLast = array_pop($aboutWords);
                @endphp
                <h2 id="about-title" class="display-lg mb-4">{{ implode(' ', $aboutWords) }} <span class="about-name">{{ $aboutLast }}</span></h2>
                <p class="about-lead">{{ $about['lead'] }}</p>
            </div>
            <div class="about-body reveal">
                @foreach ($about['body'] as $para)
                    <p>{{ $para }}</p>
                @endforeach
                <p><em>{{ $ui['principle_quote'] }}</em></p>
            </div>
        </div>

        <dl class="about-facts" data-about-facts>
            <div class="about-fact reveal">
                <dt>{{ $current['title'] }}</dt>
                <dd>{{ $ui['now'] }} · {{ $current['org'] }}</dd>
            </div>
            <div class="about-fact reveal">
                <dt>Surabaya</dt>
                <dd>{{ $ui['region'] }}</dd>
            </div>
            <div class="about-fact reveal">
                <dt><span data-flow="{{ $years }}">{{ $years }}</span><span class="about-plus">+</span></dt>
                <dd>{{ $ui['years_desc'] }}</dd>
            </div>
        </dl>
    </div>
</section>

{{-- ========== 02 SERVICES ========== --}}
<section class="banner services-split" id="services" data-scroll-expand aria-labelledby="services-title">
    <span class="svc-fill" aria-hidden="true"></span>
    <div class="container">
        <div class="svc-split">
            <div class="svc-intro">
                <span class="eyebrow-pill reveal">{{ $p['nav']['services'] }}</span>
                <h2 id="services-title" class="display-lg mb-4 reveal">{{ $b['services_title'] }}</h2>
                <p class="svc-lead reveal">{{ $b['services_sub'] }}</p>
                <a href="#contact" class="island-btn island-btn-light svc-cta reveal">
                    {{ $ui['cta_contact'] }}
                    <span class="island-btn-icon"><i class="ph-light ph-arrow-up-right"></i></span>
                </a>
                <div class="svc-progress reveal" aria-hidden="true">
                    <span class="svc-count"><strong data-svc-now>01</strong> / {{ str_pad(count($p['services']), 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="svc-bar"><i data-svc-bar></i></span>
                    <span class="svc-current" data-svc-title>{{ $p['services'][0]['title'] ?? '' }}</span>
                </div>
            </div>

            <ol class="svc-list" data-svc-spotlight>
                @foreach ($p['services'] as $service)
                    <li class="svc-item reveal">
                        <span class="svc-num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="svc-glyph" aria-hidden="true"><i class="ph-light {{ $service['icon'] }}"></i></span>
                        <div class="svc-text">
                            <h3>{{ $service['title'] }}</h3>
                            <p>{{ $service['desc'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</section>

{{-- ========== 03 EXPERIENCE ========== --}}
@php
    // One card per employer; roles inside stay newest first.
    $companies = collect($p['experiences'])->groupBy('org')->map(function ($roles, $org) {
        preg_match('/^(.*?)(?: \((.*)\))?$/', $org, $m);
        $name = $m[1];
        $words = array_values(array_filter(explode(' ', $name), fn ($w) => ! in_array($w, ['PT', 'UPT', '/'])));
        return [
            'name' => $name,
            'legal' => $m[2] ?? null,
            'mark' => strtoupper(mb_substr($words[0] ?? $name, 0, 1) . mb_substr($words[1] ?? '', 0, 1)),
            'span' => explode(' - ', $roles->last()['period'])[0] . ' – ' . explode(' - ', $roles->first()['period'])[1],
            'logo' => $roles->pluck('logo')->filter()->first(),
            'current' => $roles->contains('current', true),
            'roles' => $roles,
        ];
    });
@endphp
<section class="bento-section" id="experience" aria-labelledby="journey-title">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow-pill">{{ $p['nav']['experience'] }}</span>
            <h2 id="journey-title" class="display-lg">{{ $b['journey_title'] }}</h2>
        </div>

        <div class="xp-list">
            @foreach ($companies as $co)
                <article @class(['tile xp-company reveal', 'is-current' => $co['current']])>
                    <header class="xp-head">
                      <div class="xp-head-in">
                          @if ($co['logo'])
                              <span class="xp-mark has-logo"><img src="{{ asset($co['logo']) }}" alt="{{ $co['name'] }}" width="56" height="56" loading="lazy" decoding="async"></span>
                          @else
                              <span class="xp-mark" aria-hidden="true">{{ $co['mark'] }}</span>
                          @endif
                          <div>
                              <h3 class="xp-org">{{ $co['name'] }}</h3>
                              @if ($co['legal'])<p class="xp-legal">{{ $co['legal'] }}</p>@endif
                              <p class="xp-span">{{ $co['span'] }}</p>
                          </div>
                      </div>
                    </header>

                    <ol class="xp-roles is-path" data-xp-track>
                        @foreach ($co['roles'] as $role)
                            <li class="xp-role">
                                <div class="xp-role-top">
                                    <h4 class="xp-title">{{ $role['title'] }}</h4>
                                    <span class="xp-badge">{{ $role['type'] }}</span>
                                </div>
                                <p @class(['xp-period', 'is-now' => $role['current']])>
                                    @if ($role['current'])<span class="pulse-dot"></span>@endif
                                    {{ $role['period'] }}
                                </p>
                                <ul class="xp-points">
                                    @foreach ($role['points'] as $point)
                                        <li>{{ $point }}</li>
                                    @endforeach
                                </ul>
                                @if (! empty($role['tags']))
                                    <ul class="xp-tags" aria-label="Tools">
                                        @foreach ($role['tags'] as $tag)
                                            <li>{{ $tag }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- ========== 04 SKILLS ========== --}}
@php
    $levelRank = ['advanced' => 3, 'intermediate' => 2, 'basic' => 1];
    $skillGroups = collect($p['skills'])->groupBy('group');
    $groupIcons = ['infra' => 'ph-hard-drives', 'dev' => 'ph-code', 'creative' => 'ph-palette'];
@endphp
<section class="bento-section skills-blue" id="skills" data-scroll-bg="#3651b4" data-scroll-bg-dark="#17245a" data-scroll-fill aria-labelledby="skills-title">
    <span class="sk-fill" aria-hidden="true"></span>
    <div class="container">
      <div class="sk-panel" data-fill-from>
        <div class="section-head reveal">
            <span class="eyebrow-pill">{{ $p['nav']['skills'] }}</span>
            <h2 id="skills-title" class="display-lg">{{ $b['skills_title'] }}</h2>
        </div>
      </div>

        <div class="sk-grid">
            @foreach ($p['skill_groups'] as $gKey => $gName)
                @php $items = ($skillGroups[$gKey] ?? collect())->sortByDesc(fn ($s) => $levelRank[$s['level']])->values(); @endphp
                <section class="tile sk-card reveal" aria-labelledby="sk-{{ $gKey }}">
                    <div class="sk-head">
                        <span class="sk-icon" aria-hidden="true"><i class="ph-light {{ $groupIcons[$gKey] }}"></i></span>
                        <h3 id="sk-{{ $gKey }}" class="sk-title">{{ $gName }}</h3>
                        <p class="sk-desc">{{ $p['skill_group_desc'][$gKey] }}</p>
                    </div>

                    <ul class="sk-list">
                        @foreach ($items as $skill)
                            <li class="sk-item">
                                <span class="sk-name">{{ $skill['name'] }}</span>
                                <span class="sk-level" title="{{ $p['levels'][$skill['level']] }}">
                                    <span class="sk-meter" aria-hidden="true">
                                        @for ($i = 1; $i <= 3; $i++)
                                            <i @class(['on' => $i <= $levelRank[$skill['level']]])></i>
                                        @endfor
                                    </span>
                                    <span class="sk-level-text">{{ $p['levels'][$skill['level']] }}</span>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>
    </div>
</section>

{{-- ========== 06 EDUCATION & CERTIFICATIONS ========== --}}
<section class="bento-section" id="education" aria-labelledby="edu-title">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow-pill">{{ $p['nav']['education'] }} &amp; {{ $ui['certificates'] }}</span>
            <h2 id="edu-title" class="display-lg">{{ $b['foundation_title'] }}</h2>
        </div>

        <h3 class="sub-head reveal"><i class="ph-light ph-graduation-cap"></i> {{ $ui['education'] }} <span>{{ count($p['education']) }}</span></h3>
        <div class="bento edu-bento mb-5">
            @foreach ($p['education'] as $edu)
                <div class="tile c2 edu-card reveal">
                    <span class="tile-label"><i class="ph-light ph-graduation-cap"></i> {{ $edu['period'] }}</span>
                    <h4 class="h4 fw-bold mb-1">{{ $edu['degree'] }}</h4>
                    <div class="text-body-secondary fw-semibold mb-3">{{ $edu['school'] }}</div>
                    <ul class="text-body-secondary ps-3 mb-0">
                        @foreach ($edu['points'] as $point)
                            <li class="mb-1">{{ $point }}</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach

            <div class="tile c2 t-accent hl-card reveal">
                <div class="hl-num"><span data-flow="{{ $p['gpa'] }}" data-flow-decimals="2">{{ $p['gpa'] }}</span></div>
                <div class="hl-text">
                    <span class="tile-label"><i class="ph-light ph-calendar-check"></i> Cum laude</span>
                    <div class="hl-title">{{ $ui['gpa_desc'] }}</div>
                    <div class="small">{{ $b['semesters'] }}</div>
                </div>
            </div>

            <div class="tile c2 hl-card reveal">
                <div class="hl-icon"><i class="ph-light ph-trophy"></i></div>
                <div class="hl-text">
                    <span class="tile-label">{{ $ui['eis_label'] }}</span>
                    <div class="hl-title">{{ $ui['eis_title'] }}</div>
                    <div class="small text-body-secondary">{{ $ui['eis_desc'] }}</div>
                </div>
            </div>
        </div>

        @php
            $initials = function ($s) {
                $words = array_values(array_filter(preg_split('/\s+/', trim(preg_replace('/\(.*?\)/', '', (string) $s)))));
                if (count($words) > 1) return mb_strtoupper(mb_substr($words[0], 0, 1).mb_substr($words[1], 0, 1));
                $w = $words[0] ?? '?';
                if (mb_strlen($w) <= 4) return mb_strtoupper($w);
                return preg_match('/^.[^A-Z]*([A-Z])/', $w, $m) ? mb_strtoupper(mb_substr($w, 0, 1)).$m[1] : mb_strtoupper(mb_substr($w, 0, 2));
            };
            $creds = collect($p['certificates'])->map(fn ($c) => $c + ['kind' => 'cert', 'org' => $c['short'] ?? $c['issuer']])
                ->concat(collect($p['badges'] ?? [])->map(fn ($c) => $c + ['kind' => 'badge', 'org' => $c['issuer']]));
        @endphp
        @foreach (['cert' => ['label' => $ui['certificates'], 'icon' => 'ph-certificate'], 'badge' => ['label' => $ui['badges'], 'icon' => 'ph-seal-check']] as $kind => $group)
            @php $items = $creds->where('kind', $kind); @endphp
            @continue($items->isEmpty())
            <h3 @class(['sub-head reveal', 'mt-5' => ! $loop->first])><i class="ph-light {{ $group['icon'] }}"></i> {{ $group['label'] }} <span>{{ $items->count() }}</span></h3>
            <div class="cred-grid">
                @foreach ($items as $cred)
                    <article @class(['tile cred-card reveal', 'is-badge' => $kind === 'badge', 'is-cert' => $kind === 'cert', 'has-link' => ! empty($cred['url'])])>
                        <div class="cred-top">
                            @if (! empty($cred['logo']))
                                <span class="cred-mark has-logo"><img src="{{ asset($cred['logo']) }}" alt="{{ $cred['org'] }}" width="48" height="48" loading="lazy" decoding="async"></span>
                            @else
                                <span class="cred-mark" aria-hidden="true">{{ $initials($cred['org']) }}</span>
                            @endif
                            <span class="cred-date"><i class="ph-light ph-calendar-blank"></i> {{ $cred['date'] }}</span>
                        </div>
                        <h4 class="cred-title">{{ $cred['title'] }}</h4>
                        <div class="cred-issuer">{{ $cred['issuer'] }}</div>
                        @if (! empty($cred['credential_id']) || ! empty($cred['url']))
                            <div class="cred-foot">
                                @if (! empty($cred['credential_id']))<span class="cred-id" title="{{ $ui['credential_id'] }}">{{ $ui['cred_label'] }} {{ $cred['credential_id'] }}</span>@endif
                                @if (! empty($cred['url']))
                                    <a class="cred-go" href="{{ $cred['url'] }}" target="_blank" rel="noopener" aria-label="{{ $ui['show_credential'] }}: {{ $cred['title'] }}" title="{{ $ui['show_credential'] }}">
                                        <span class="island-btn-icon"><i class="ph-light ph-arrow-up-right"></i></span>
                                    </a>
                                @endif
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        @endforeach
    </div>
</section>

{{-- ========== PUBLICATIONS ========== --}}
@if (! empty($p['publications']))
<section class="bento-section pub-section" id="publications" aria-labelledby="pub-title">
    <div class="container pub-split">
        <div class="pub-intro reveal">
            <span class="eyebrow-pill">{{ $p['nav']['publications'] }}</span>
            <h2 id="pub-title" class="display-lg">{{ $b['publications_title'] }}</h2>
            <p class="pub-count"><strong>{{ count($p['publications']) }}</strong> {{ $ui['pub_count'] }}</p>
        </div>

        @php
            // ALL-CAPS titles read as shouting: show them in title case (stored text is unchanged)
            $pubTitle = function (string $t) {
                $letters = preg_replace('/[^\p{L}]/u', '', $t);
                if ($letters === '' || mb_strtoupper($letters) !== $letters) return $t;
                $small = ['dan', 'di', 'ke', 'dari', 'pada', 'untuk', 'dengan', 'yang', 'sebagai', 'atau', 'of', 'and', 'the', 'in', 'on', 'for', 'to', 'a', 'an', 'with', 'by'];
                $acronyms = ['UPT', 'TIK', 'UPN', 'IT', 'AI', 'IOT', 'API', 'CMS', 'ERP', 'UI', 'UX', 'SITASI', 'PHP', 'SQL', 'MVC', 'GIS', 'SPK', 'DSS', 'RFID', 'CCTV', 'LAN', 'WAN', 'VPN', 'DNS', 'ISO', 'BNSP', 'KKNI', 'UMKM', 'SDM', 'TI', 'SI', 'II', 'III', 'IV'];
                $out = [];
                foreach (preg_split('/(\s+)/u', $t, -1, PREG_SPLIT_DELIM_CAPTURE) as $i => $word) {
                    if (trim($word) === '') { $out[] = $word; continue; }
                    $lower = mb_strtolower($word);
                    if ($i > 0 && in_array($lower, $small, true)) { $out[] = $lower; continue; }
                    // Title-case each hyphen part, keep known acronyms in capitals
                    $parts = array_map(function ($part) use ($acronyms) {
                        $core = preg_replace('/[^\p{L}]/u', '', $part);
                        if (in_array(mb_strtoupper($core), $acronyms, true)) return $part;
                        $low = mb_strtolower($part);
                        return preg_replace_callback('/\p{L}/u', fn ($m) => mb_strtoupper($m[0]), $low, 1);
                    }, explode('-', $word));
                    $out[] = implode('-', $parts);
                }
                return implode('', $out);
            };
            $ownName = mb_strtolower(trim($p['name']));
        @endphp
        <ol class="pub-list pub-rows">
            @foreach ($p['publications'] as $pub)
                @php
                    $authors = trim((string) ($pub['authors'] ?? ''));
                    $showAuthors = $authors !== '' && mb_strtolower($authors) !== $ownName;
                    $hasLink = ! empty($pub['url']);
                @endphp
                <li @class(['tile pub-row reveal', 'has-link' => $hasLink])>
                    <div class="pub-row-main">
                        <div class="pub-meta">
                            @if (! empty($pub['year']))<span class="pub-year">{{ $pub['year'] }}</span>@endif
                            @if (! empty($pub['type']))<span class="pub-type">{{ $ui['pub_types'][$pub['type']] ?? $pub['type'] }}</span>@endif
                            @if (! empty($pub['venue']))<span class="pub-venue-inline"><i class="ph-light ph-book-open-text"></i> {{ $pub['venue'] }}</span>@endif
                        </div>
                        <h3 class="pub-title" title="{{ $pub['title'] }}">{{ $pubTitle($pub['title']) }}</h3>
                        @if ($showAuthors)<p class="pub-authors">{{ $authors }}</p>@endif
                        @if (! empty($pub['abstract']))<p class="pub-abstract">{{ $pub['abstract'] }}</p>@endif
                    </div>
                    @if ($hasLink)
                        <a href="{{ $pub['url'] }}" class="pub-go" target="_blank" rel="noopener" aria-label="{{ $ui['pub_read'] }}: {{ $pub['title'] }}" title="{{ $ui['pub_read'] }}">
                            <span class="island-btn-icon"><i class="ph-light ph-arrow-up-right"></i></span>
                        </a>
                    @endif
                </li>
            @endforeach
        </ol>
    </div>
</section>
@endif

{{-- ========== 05 PROJECTS ========== --}}
<section class="banner banner-soft" id="projects" data-scroll-bg="#e3e9fb" data-scroll-bg-dark="#121a36" aria-labelledby="projects-title">
    <span class="glow g1"></span>
    <div class="container">
        <span class="eyebrow-pill reveal">{{ $p['nav']['projects'] }}</span>
        <h2 id="projects-title" class="display-lg mb-3 reveal">{{ $b['projects_title'] }}</h2>
        <p class="lead-xl reveal">{{ $b['projects_sub'] }}</p>

        @php
            $works = collect($p['projects'])->map(function ($w) {
                $w['type'] = in_array($w['type'] ?? null, ['project', 'image', 'video'], true) ? $w['type'] : 'project';
                $w['embed'] = null;
                $v = $w['video'] ?? '';
                if ($v) {
                    if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([\w-]{11})~', $v, $m)) {
                        $w['embed'] = ['kind' => 'iframe', 'src' => "https://www.youtube-nocookie.com/embed/{$m[1]}?autoplay=1&rel=0"];
                        $w['image'] ??= "https://i.ytimg.com/vi/{$m[1]}/hqdefault.jpg";
                    } elseif (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $v, $m)) {
                        $w['embed'] = ['kind' => 'iframe', 'src' => "https://player.vimeo.com/video/{$m[1]}?autoplay=1"];
                    } else {
                        $w['embed'] = ['kind' => 'video', 'src' => $v];
                    }
                }
                $w['src'] = empty($w['image']) ? null : (str_starts_with($w['image'], 'http') ? $w['image'] : asset($w['image']));
                return $w;
            });
            $typeMeta = [
                'project' => ['label' => $ui['work_dev'] ?? 'Development', 'icon' => 'ph-code'],
                'image' => ['label' => $ui['work_photo'] ?? 'Photo & Design', 'icon' => 'ph-image'],
                'video' => ['label' => $ui['work_video'] ?? 'Video', 'icon' => 'ph-film-slate'],
            ];
            $present = $works->pluck('type')->unique();
            $workData = $works->map(fn ($w) => [
                'type' => $w['type'], 'title' => $w['title'], 'client' => $w['client'] ?? null, 'description' => $w['description'] ?? null,
                'src' => $w['src'], 'embed' => $w['embed'], 'link' => $w['link'] ?? null, 'tech' => (array) ($w['tech'] ?? []),
            ])->values();
        @endphp

        @php $activeTab = collect(array_keys($typeMeta))->first(fn ($t) => $present->contains($t)) ?? 'project'; @endphp
        <div class="work-filter reveal" role="tablist" aria-label="{{ $p['nav']['projects'] }}">
            @foreach ($typeMeta as $t => $meta)
                <button type="button" id="tab-{{ $t }}" @class(['is-active' => $t === $activeTab]) data-filter="{{ $t }}" role="tab"
                        aria-selected="{{ $t === $activeTab ? 'true' : 'false' }}" aria-controls="work-panel">
                    <i class="ph-light {{ $meta['icon'] }}"></i> {{ $meta['label'] }} <span>{{ $works->where('type', $t)->count() }}</span>
                </button>
            @endforeach
        </div>

        <p class="work-empty" data-work-empty @if ($present->contains($activeTab)) hidden @endif>
            <i class="ph-light ph-hourglass-medium"></i> {{ $ui['work_empty'] ?? 'New work coming soon.' }}
        </p>

        <div class="proj-row work-grid" id="work-panel" role="tabpanel" aria-labelledby="tab-{{ $activeTab }}" data-work-grid>
            @php $shownInTab = 0; @endphp
            @foreach ($works as $i => $work)
                @php $show = $work['type'] === $activeTab && $shownInTab++ < 6; @endphp
                <article class="proj-card work-card reveal" data-type="{{ $work['type'] }}" @unless ($show) hidden @endunless>
                    <button type="button" class="work-open" data-work="{{ $i }}" aria-label="{{ $work['title'] }}">
                        <div class="proj-visual">
                            @if ($work['src'])
                                <img src="{{ $work['src'] }}" alt="{{ $work['title'] }}" loading="lazy" decoding="async" width="1200" height="800">
                            @else
                                <i class="ph-light {{ $work['icon'] ?? $typeMeta[$work['type']]['icon'] }} work-placeholder"></i>
                            @endif
                            <span class="work-type"><i class="ph-light {{ $typeMeta[$work['type']]['icon'] }}"></i> {{ $typeMeta[$work['type']]['label'] }}</span>
                            @if ($work['type'] === 'video')<span class="work-play"><i class="ph-fill ph-play"></i></span>@endif
                        </div>
                    </button>
                    <div class="proj-body">
                        @if (! empty($work['category']))<span class="badge bg-light-primary mb-2">{{ $work['category'] }}</span>@endif
                        <h3 class="h5 fw-bold mb-1">{{ $work['title'] }}</h3>
                        @if (! empty($work['client']))<p class="text-body-secondary mb-0">{{ $work['client'] }}</p>@endif
                        @if (! empty($work['tech']))
                            <div class="work-tech">@foreach ((array) $work['tech'] as $t)<span>{{ $t }}</span>@endforeach</div>
                        @endif
                        @if (! empty($work['link']))
                            <a href="{{ $work['link'] }}" class="link-arrow small mt-2 d-inline-block" target="_blank" rel="noopener">{{ $ui['work_visit'] ?? 'Visit site' }} <i class="ph-light ph-arrow-up-right"></i></a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <div class="work-more" data-work-more @if ($works->where('type', $activeTab)->count() <= 6) hidden @endif>
            <button type="button" class="island-btn island-btn-primary" aria-controls="work-panel" aria-expanded="false">
                {{ $ui['work_more'] ?? 'View more' }} <span class="work-more-count">+<span data-more-count>{{ max(0, $works->where('type', $activeTab)->count() - 6) }}</span></span>
                <span class="island-btn-icon"><i class="ph-light ph-caret-down"></i></span>
            </button>
        </div>

        <dialog class="work-viewer" data-work-viewer data-visit="{{ $ui['work_visit'] }}" aria-label="{{ $p['nav']['projects'] }}">
            <button type="button" class="work-close" data-work-close aria-label="Close"><i class="ph-light ph-x"></i></button>
            <div class="work-stage" data-work-stage></div>
            <div class="work-caption" data-work-caption></div>
        </dialog>
        <script type="application/json" id="work-data">@json($workData)</script>
    </div>
</section>

{{-- ========== 07 CONTACT ========== --}}
<section class="banner banner-blue" id="contact" aria-labelledby="contact-title">
    <span class="glow g1"></span>
    <span class="glow g3"></span>
    <div class="container">
        <span class="eyebrow-pill reveal">{{ $p['nav']['contact'] }}</span>
        <h2 id="contact-title" class="display-xl mb-3 reveal">
            {{ $b['cta_title'] }}<br><span class="text-accent">{{ $b['cta_title_accent'] }}</span>
        </h2>
        <p class="lead-xl reveal">{{ $b['cta_sub'] }}</p>
        <div class="cta-actions reveal">
            <a class="island-btn island-btn-light" href="https://wa.me/{{ $p['whatsapp'] }}" target="_blank" rel="noopener">
                <i class="ph-light ph-whatsapp-logo"></i> WhatsApp
                <span class="island-btn-icon"><i class="ph-light ph-arrow-up-right"></i></span>
            </a>
            <a class="island-btn island-btn-ghost" href="mailto:{{ $p['email'] }}">
                <i class="ph-light ph-envelope-simple"></i> {{ $p['email'] }}
                <span class="island-btn-icon"><i class="ph-light ph-arrow-up-right"></i></span>
            </a>
        </div>
        <div class="reveal">@include('partials.socials', ['variant' => 'pills'])</div>
    </div>
</section>
@endsection
