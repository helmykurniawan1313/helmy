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
    <img class="photo-banner-img"
         src="{{ asset('images/server-room-1200.jpg') }}"
         srcset="{{ asset('images/server-room-1200.jpg') }} 1200w, {{ asset('images/server-room-2400.jpg') }} 2400w"
         sizes="100vw" width="2400" height="1350" loading="lazy" decoding="async"
         alt="{{ $b['feature_alt'] }}">
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
                <h2 id="about-title" class="display-lg mb-4">{{ $about['title'] }}</h2>
                <p class="about-lead">{{ $about['lead'] }}</p>
            </div>
            <div class="about-body reveal">
                @foreach ($about['body'] as $para)
                    <p>{{ $para }}</p>
                @endforeach
                <p><em>{{ $ui['principle_quote'] }}</em></p>
            </div>
        </div>

        <dl class="about-facts">
            <div class="about-fact reveal">
                <dt>{{ $current['title'] }}</dt>
                <dd>{{ $ui['now'] }} · {{ $current['org'] }}</dd>
            </div>
            <div class="about-fact reveal">
                <dt>Surabaya</dt>
                <dd>{{ $ui['region'] }}</dd>
            </div>
            <div class="about-fact reveal">
                <dt>{{ $years }}+</dt>
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
            </div>

            <ol class="svc-list">
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
                        <span class="xp-mark" aria-hidden="true">{{ $co['mark'] }}</span>
                        <div>
                            <h3 class="xp-org">{{ $co['name'] }}</h3>
                            @if ($co['legal'])<p class="xp-legal">{{ $co['legal'] }}</p>@endif
                            <p class="xp-span">{{ $co['span'] }}</p>
                        </div>
                    </header>

                    <ol @class(['xp-roles', 'is-path' => $co['roles']->count() > 1])>
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
<section class="bento-section" id="skills" aria-labelledby="skills-title">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow-pill">{{ $p['nav']['skills'] }}</span>
            <h2 id="skills-title" class="display-lg">{{ $b['skills_title'] }}</h2>
        </div>

        <div class="sk-grid">
            @foreach ($p['skill_groups'] as $gKey => $gName)
                @php $items = ($skillGroups[$gKey] ?? collect())->sortByDesc(fn ($s) => $levelRank[$s['level']])->values(); @endphp
                <section class="tile sk-card reveal" aria-labelledby="sk-{{ $gKey }}">
                    <span class="sk-icon" aria-hidden="true"><i class="ph-light {{ $groupIcons[$gKey] }}"></i></span>
                    <h3 id="sk-{{ $gKey }}" class="sk-title">{{ $gName }}</h3>
                    <p class="sk-desc">{{ $p['skill_group_desc'][$gKey] }}</p>

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

{{-- ========== 05 PROJECTS ========== --}}
<section class="banner banner-soft" id="projects" data-scroll-bg="#e3e9fb" data-scroll-bg-dark="#121a36" aria-labelledby="projects-title">
    <span class="glow g1"></span>
    <div class="container">
        <span class="eyebrow-pill reveal">{{ $p['nav']['projects'] }}</span>
        <h2 id="projects-title" class="display-lg mb-3 reveal">{{ $b['projects_title'] }}</h2>
        <p class="lead-xl reveal">{{ $b['projects_sub'] }}</p>

        <div class="proj-row">
            @foreach ($p['projects'] as $project)
                <article @class(["proj-card reveal", "proj-featured" => $loop->first])>
                    <div class="proj-visual"><img src="{{ asset($project['image']) }}" alt="{{ $project['title'] }} – {{ $project['client'] }}" loading="lazy" decoding="async" width="1200" height="800"></div>
                    <div class="proj-body">
                        <span class="badge bg-light-primary mb-2">{{ $project['category'] }}</span>
                        <h3 class="h5 fw-bold mb-1">{{ $project['title'] }}</h3>
                        <p class="text-body-secondary mb-0">{{ $project['client'] }}</p>
                    </div>
                </article>
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

        <h3 class="h5 fw-semibold mb-3 reveal">{{ $ui['education'] }}</h3>
        <div class="bento mb-5">
            @foreach ($p['education'] as $edu)
                <div class="tile c2 reveal {{ $loop->first ? 'r2' : '' }}">
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

            <div class="tile t-accent reveal">
                <span class="tile-label"><i class="ph-light ph-calendar-check"></i> Cum laude</span>
                <div class="tile-big mt-auto"><span data-flow="{{ $p['gpa'] }}" data-flow-decimals="2">{{ $p['gpa'] }}</span></div>
                <div class="small text-body-secondary">{{ $ui['gpa_desc'] }} · {{ $b['semesters'] }}</div>
            </div>

            <div class="tile reveal">
                <span class="tile-label"><i class="ph-light ph-trophy"></i> EIS 2019</span>
                <div class="h5 fw-bold mt-auto mb-1">Google Indonesia</div>
                <div class="small text-body-secondary">Data Science Indonesia · Gapura Digital</div>
            </div>
        </div>

        <h3 class="h5 fw-semibold mb-3 reveal">{{ $ui['certificates'] }}</h3>
        <div class="bento">
            @foreach ($p['certificates'] as $cert)
                <div class="tile reveal">
                    <span class="tile-label"><i class="ph-light ph-medal"></i> {{ $cert['short'] }} · {{ $cert['date'] }}</span>
                    <div class="h6 fw-bold mt-auto mb-1">{{ $cert['title'] }}</div>
                    <div class="small text-body-secondary">{{ $cert['issuer'] }}</div>
                </div>
            @endforeach
        </div>
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
    </div>
</section>
@endsection
