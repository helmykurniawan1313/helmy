@extends('admin.layouts.auth')

@section('title', 'Log in')

@section('content')
<div class="login-shell">
    {{-- Form side --}}
    <main class="login-main">
        <div class="login-top">
            <a href="{{ route('home') }}" class="auth-logo-text">Helmy<span>.</span></a>
            <a href="{{ route('home') }}" class="login-back"><i class="bi bi-arrow-left"></i> Website</a>
        </div>

        <div class="login-card">
            <span class="login-eyebrow">Admin</span>
            <h1 class="login-title">Welcome back</h1>
            <p class="login-sub">Log in to manage your portfolio content.</p>

            @if ($errors->any())
                <div class="login-alert" role="alert">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login') }}" data-login-form novalidate>
                @csrf

                <div class="login-field">
                    <label for="email">Email</label>
                    <div class="login-input">
                        <i class="bi bi-envelope"></i>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                               @class(['is-invalid' => $errors->has('email')])
                               placeholder="you@example.com" autocomplete="username" inputmode="email" required autofocus>
                    </div>
                </div>

                <div class="login-field">
                    <label for="password">Password</label>
                    <div class="login-input">
                        <i class="bi bi-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Your password"
                               autocomplete="current-password" required data-password>
                        <button type="button" class="login-eye" data-toggle-password aria-label="Show password" aria-pressed="false">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <p class="login-caps" data-caps hidden><i class="bi bi-capslock-fill"></i> Caps Lock is on</p>
                </div>

                <label class="login-remember" for="remember">
                    <input type="checkbox" name="remember" value="1" id="remember" @checked(old('remember'))>
                    <span>Keep me logged in</span>
                </label>

                <button type="submit" class="login-submit" data-login-submit>
                    <span class="login-submit-label">Log in</span>
                    <span class="login-submit-icon"><i class="bi bi-arrow-right"></i></span>
                </button>
            </form>
        </div>

        <p class="login-foot">Protected area · {{ parse_url(config('app.url'), PHP_URL_HOST) ?: 'helmy.my.id' }}</p>
    </main>

    {{-- Brand side --}}
    <aside class="login-brand" aria-hidden="true">
        <div class="login-brand-inner">
            <span class="login-brand-pill">Portfolio admin</span>
            <h2>Keep the website<br>fresh in minutes.</h2>
            <p>Edit every section in English and Indonesian, manage AI keys and your resume, all in one place.</p>

            <ul class="login-preview">
                <li><span class="lp-icon"><i class="bi bi-briefcase"></i></span><span><strong>Experience &amp; skills</strong><small>Roles, tools and levels</small></span></li>
                <li><span class="lp-icon"><i class="bi bi-collection-play"></i></span><span><strong>Portfolio</strong><small>Projects, photos and videos</small></span></li>
                <li><span class="lp-icon"><i class="bi bi-stars"></i></span><span><strong>AI &amp; resume</strong><small>Chat keys and ATS PDF</small></span></li>
            </ul>
        </div>
    </aside>
</div>

<script>
    (function () {
        var form = document.querySelector('[data-login-form]');
        var pass = document.querySelector('[data-password]');
        var eye = document.querySelector('[data-toggle-password]');
        var caps = document.querySelector('[data-caps]');
        var submit = document.querySelector('[data-login-submit]');

        eye && eye.addEventListener('click', function () {
            var show = pass.type === 'password';
            pass.type = show ? 'text' : 'password';
            eye.setAttribute('aria-pressed', String(show));
            eye.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            eye.innerHTML = '<i class="bi bi-eye' + (show ? '-slash' : '') + '"></i>';
            pass.focus();
        });

        function checkCaps(e) { if (e.getModifierState) caps.hidden = !e.getModifierState('CapsLock'); }
        pass && pass.addEventListener('keydown', checkCaps);
        pass && pass.addEventListener('keyup', checkCaps);
        pass && pass.addEventListener('blur', function () { caps.hidden = true; });

        form && form.addEventListener('submit', function (e) {
            if (!form.checkValidity()) { e.preventDefault(); form.reportValidity(); return; }
            submit.disabled = true;
            submit.classList.add('is-loading');
            submit.querySelector('.login-submit-label').textContent = 'Logging in…';
            submit.querySelector(".login-submit-icon").innerHTML = "<i class=\"bi bi-arrow-repeat\"></i>";
        });
    })();
</script>
@endsection
