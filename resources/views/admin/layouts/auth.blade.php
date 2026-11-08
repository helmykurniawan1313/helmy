<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') - Helmy Admin</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <script>
        (function () {
            try { var t = localStorage.getItem('theme'); if (t) document.documentElement.setAttribute('data-bs-theme', t); } catch (e) {}
        })();
    </script>
    @vite(['resources/scss/admin.scss'])
</head>
<body>
    <div id="auth">
        @yield('content')
    </div>
</body>
</html>
