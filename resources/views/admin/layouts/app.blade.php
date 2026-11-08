@php
    $current = request()->route('section');
    $user = auth()->user();
    $initials = collect(explode(' ', $user->name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
@endphp
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
    @vite(['resources/scss/admin.scss', 'resources/js/admin.js'])
</head>
<body class="admin">
<div id="app">
    <div id="sidebar">
        <div class="sidebar-wrapper active">
            <div class="sidebar-header position-relative">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="logo">
                        <a href="{{ route('admin.dashboard') }}">Helmy<span>.</span></a>
                        <small class="logo-sub">Admin</small>
                    </div>
                    <div class="sidebar-toggler x">
                        <a href="#" class="sidebar-hide d-xl-none d-block" aria-label="Close menu"><i class="bi bi-x bi-middle"></i></a>
                    </div>
                </div>
            </div>
            <div class="sidebar-menu">
                <ul class="menu">
                    <li @class(['sidebar-item', 'active' => request()->routeIs('admin.dashboard')])>
                        <a href="{{ route('admin.dashboard') }}" class="sidebar-link">
                            <i class="bi bi-grid-fill"></i><span>Dashboard</span>
                        </a>
                    </li>

                    <li class="sidebar-title">Content</li>
                    @foreach (App\Admin\Sections::grouped() as $group => $sections)
                        @php $open = array_key_exists((string) $current, $sections); @endphp
                        <li @class(['sidebar-item has-sub', 'active' => $open])>
                            <a href="#" class="sidebar-link">
                                <i class="bi bi-{{ App\Admin\Sections::GROUP_ICONS[$group] ?? 'folder' }}"></i>
                                <span>{{ $group }}</span>
                            </a>
                            <ul class="submenu">
                                @foreach ($sections as $key => $s)
                                    <li @class(['submenu-item', 'active' => $current === $key])>
                                        <a href="{{ route('admin.sections.edit', $key) }}" class="submenu-link">{{ $s['title'] }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @endforeach

                    <li class="sidebar-title">Settings</li>
                    <li @class(['sidebar-item', 'active' => request()->routeIs('admin.settings.*')])>
                        <a href="{{ route('admin.settings.ai') }}" class="sidebar-link">
                            <i class="bi bi-key-fill"></i><span>AI keys &amp; models</span>
                        </a>
                    </li>
                    <li @class(['sidebar-item', 'active' => request()->routeIs('admin.resume*')])>
                        <a href="{{ route('admin.resume') }}" class="sidebar-link">
                            <i class="bi bi-file-earmark-person-fill"></i><span>Resume (PDF)</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div id="main">
        <header class="admin-topbar">
            <a href="#" class="burger-btn d-block d-xl-none" aria-label="Open menu"><i class="bi bi-justify fs-3"></i></a>

            <div class="ms-auto d-flex align-items-center gap-2">
                <a href="{{ route('home') }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-box-arrow-up-right"></i><span class="d-none d-sm-inline ms-1">View website</span>
                </a>

                <button type="button" class="btn btn-sm btn-icon theme-btn" data-theme-toggle aria-label="Toggle dark mode" title="Toggle dark mode">
                    <i class="bi bi-moon-stars theme-icon-light"></i><i class="bi bi-sun theme-icon-dark"></i>
                </button>

                <div class="dropdown">
                    <button class="btn btn-sm user-btn d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="avatar bg-primary"><span class="avatar-content">{{ $initials }}</span></span>
                        <span class="d-none d-md-inline text-start lh-sm">
                            <span class="d-block fw-bold">{{ $user->name }}</span>
                            <span class="d-block text-muted small">{{ $user->email }}</span>
                        </span>
                        <i class="bi bi-chevron-down small text-muted"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="bi bi-grid me-2"></i>Dashboard</a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.settings.ai') }}"><i class="bi bi-key me-2"></i>AI settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-left me-2"></i>Log out</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <div class="main-content">
            <div class="page-heading d-flex flex-wrap align-items-end gap-3">
                <div class="me-auto">
                    @hasSection('eyebrow')<div class="page-eyebrow">@yield('eyebrow')</div>@endif
                    <h3 class="mb-1">@yield('title')</h3>
                    @hasSection('subtitle')<p class="text-muted mb-0">@yield('subtitle')</p>@endif
                </div>
                @yield('actions')
            </div>

            @if (session('status'))
                <div class="alert alert-light-success color-success alert-dismissible fade show" role="status">
                    <i class="bi bi-check-circle-fill me-1"></i> {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-light-danger color-danger" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-1"></i>
                    {{ $errors->count() === 1 ? $errors->first() : 'Please fix the '.$errors->count().' highlighted fields.' }}
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</div>
</body>
</html>
