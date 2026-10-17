@php
    $locale = app()->getLocale();
    $locales = $p['locales'];
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $p['meta']['title'] }}</title>
    <meta name="description" content="{{ $p['meta']['description'] }}">
    <meta name="author" content="{{ $p['name'] }}">
    <meta name="robots" content="@yield('robots', 'index, follow')">
    <link rel="canonical" href="{{ route($locales[$locale]['route']) }}">

    {{-- Language alternates for search engines --}}
    @foreach ($locales as $code => $l)
        <link rel="alternate" hreflang="{{ $code }}" href="{{ route($l['route']) }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ route('home') }}">

    {{-- Open Graph / Twitter --}}
    <meta property="og:type" content="profile">
    <meta property="og:site_name" content="{{ $p['name'] }}">
    <meta property="og:locale" content="{{ $locales[$locale]['og'] }}">
    @foreach ($locales as $code => $l)
        @if ($code !== $locale)
            <meta property="og:locale:alternate" content="{{ $l['og'] }}">
        @endif
    @endforeach
    <meta property="og:title" content="{{ $p['meta']['title'] }}">
    <meta property="og:description" content="{{ $p['meta']['description'] }}">
    <meta property="og:url" content="{{ route($locales[$locale]['route']) }}">
    <meta property="og:image" content="{{ asset($p['photo']) }}">
    <meta name="twitter:card" content="summary">

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    <meta name="theme-color" content="#435ebe">

    {{-- Apply saved / system theme before paint to avoid a flash --}}
    <script>
        (function () {
            var t = null;
            try { t = localStorage.getItem('theme'); } catch (e) {}
            if (!t && window.matchMedia) t = matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            document.documentElement.setAttribute('data-bs-theme', t || 'light');
            document.documentElement.classList.add('js');
        })();
    </script>

    @vite(['resources/scss/app.scss', 'resources/js/app.js'])

    @stack('schema')
</head>
<body class="portfolio">
    <a class="skip-link" href="#content">{{ $p['ui']['skip'] }}</a>
    @include('partials.navbar')

    <main id="content" tabindex="-1">
        @yield('content')
    </main>

    @include('partials.footer')
    @includeWhen(isset($p['assistant']), 'partials.assistant')
</body>
</html>
