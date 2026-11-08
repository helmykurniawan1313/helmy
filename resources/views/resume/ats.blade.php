<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>{{ $r['name'] }} - Resume</title>
<style>
    /* ATS-friendly: one column, real text, standard headings, no tables/images/icons */
    @page { margin: 14mm 15mm 14mm 15mm; }
    body { font-family: "DejaVu Sans", Arial, sans-serif; font-size: 9.4pt; line-height: 1.32; color: #111; }
    h1 { font-size: 20pt; margin: 0; letter-spacing: 0.2pt; }
    .headline { font-size: 11pt; margin: 2pt 0 4pt; color: #222; }
    .contact { font-size: 9pt; color: #333; margin: 0 0 8pt; }
    h2 { font-size: 10.5pt; text-transform: uppercase; letter-spacing: 0.6pt; margin: 10pt 0 4pt; page-break-after: avoid; padding-bottom: 2pt; border-bottom: 0.8pt solid #444; }
    p { margin: 0 0 4pt; }
    ul { margin: 2pt 0 0 0; padding-left: 13pt; }
    li { margin: 0 0 1.5pt; }
    .item { margin: 0 0 6pt; }
    .row-title, .row-sub { page-break-after: avoid; }
    li { page-break-inside: avoid; }
    .row-title { font-weight: bold; font-size: 10pt; }
    .row-sub { color: #333; }
    .muted { color: #444; font-size: 9pt; }
    .skills-line { margin: 0 0 2pt; }
</style>
</head>
<body>
    <h1>{{ $r['name'] }}</h1>
    <div class="headline">{{ $r['headline'] }}</div>
    <div class="contact">{{ implode(' | ', $r['contact']) }}</div>

    @if ($r['summary'])
        <h2>{{ $r['labels']['summary'] }}</h2>
        <p>{{ $r['summary'] }}</p>
    @endif

    @if ($r['core_skills'])
        <h2>{{ $r['labels']['core'] }}</h2>
        <p>{{ implode(' | ', $r['core_skills']) }}</p>
    @endif

    <h2>{{ $r['labels']['experience'] }}</h2>
    @foreach ($r['experience'] as $job)
        <div class="item">
            <div class="row-title">{{ $job['title'] }}</div>
            <div class="row-sub">{{ $job['org'] }}@if ($job['type']) | {{ $job['type'] }}@endif | {{ $job['period'] }}</div>
            @if ($job['points'])
                <ul>
                    @foreach ($job['points'] as $point)
                        <li>{{ $point }}</li>
                    @endforeach
                </ul>
            @endif
            @if ($job['tags'])
                <div class="muted">{{ $r['labels']['tools'] }}: {{ implode(', ', $job['tags']) }}</div>
            @endif
        </div>
    @endforeach

    <h2>{{ $r['labels']['education'] }}</h2>
    @foreach ($r['education'] as $edu)
        <div class="item">
            <div class="row-title">{{ $edu['degree'] }}</div>
            <div class="row-sub">{{ $edu['school'] }} | {{ $edu['period'] }}</div>
            @if ($edu['points'])
                <ul>
                    @foreach ($edu['points'] as $point)
                        <li>{{ $point }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endforeach

    @if ($r['skills'])
        <h2>{{ $r['labels']['skills'] }}</h2>
        @foreach ($r['skills'] as $group)
            <p class="skills-line"><strong>{{ $group['group'] }}:</strong> {{ implode(', ', $group['items']) }}</p>
        @endforeach
    @endif

    @if ($r['certificates'])
        <h2>{{ $r['labels']['certs'] }}</h2>
        <ul>
            @foreach ($r['certificates'] as $c)
                <li>{{ $c['title'] }} - {{ $c['issuer'] }}@if ($c['date']) ({{ $c['date'] }})@endif</li>
            @endforeach
        </ul>
    @endif

    @if ($r['badges'])
        <h2>{{ $r['labels']['badges'] }}</h2>
        <ul>
            @foreach ($r['badges'] as $c)
                <li>{{ $c['title'] }} - {{ $c['issuer'] }}@if ($c['date']) ({{ $c['date'] }})@endif</li>
            @endforeach
        </ul>
    @endif

    @if ($r['publications'])
        <h2>{{ $r['labels']['pubs'] }}</h2>
        <ul>
            @foreach ($r['publications'] as $x)
                <li>{{ $x['title'] }}@if ($x['venue']), {{ $x['venue'] }}@endif @if ($x['year'])({{ $x['year'] }})@endif</li>
            @endforeach
        </ul>
    @endif
</body>
</html>
