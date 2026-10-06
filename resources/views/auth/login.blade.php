<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f3d24">
    <meta name="robots" content="noindex, nofollow">
    <title>Sign in · Demo HRMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --green-950: #0b2d1b; --green-900: #0f3d24; --green-700: #1a6b3c; --green-600: #237a49;
            --green-400: #55a678; --green-100: #e3f1e8; --green-50: #f3faf5;
            --ink: #14191a; --ink-2: #4b5563; --ink-3: #8a9099;
            --line: #e4e8e6; --bg: #f6f8f7; --danger: #c0392b; --danger-bg: #fdf2f1;
            --ease: cubic-bezier(.22, 1, .36, 1);
        }
        * { box-sizing: border-box; }
        html { background: var(--bg); }
        body { margin: 0; min-height: 100vh; color: var(--ink); background: var(--bg); font-family: 'Plus Jakarta Sans', system-ui, sans-serif; -webkit-font-smoothing: antialiased; }
        a { color: inherit; text-decoration: none; }
        button, input { font: inherit; }

        .shell { display: grid; grid-template-columns: minmax(0, 1.05fr) minmax(0, 1fr); min-height: 100vh; }

        /* ---------- Brand panel ---------- */
        .aside { position: relative; display: flex; flex-direction: column; justify-content: space-between; overflow: hidden; padding: 2.5rem 3.5rem; color: #fff; background: radial-gradient(120% 90% at 0% 0%, #1d7a45 0%, var(--green-900) 55%, var(--green-950) 100%); }
        .aside::after { content: ""; position: absolute; inset: 0; pointer-events: none; background-image: linear-gradient(rgba(255,255,255,.045) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.045) 1px, transparent 1px); background-size: 44px 44px; mask-image: radial-gradient(70% 70% at 30% 40%, #000, transparent); }
        .aside > * { position: relative; z-index: 1; }
        .brand { display: inline-flex; align-items: center; gap: 12px; }
        .mark { display: flex; align-items: flex-end; gap: 3px; height: 28px; }
        .mark i { display: block; width: 7px; border-radius: 6px 6px 2px 2px; transform: skew(-13deg); background: var(--green-700); }
        .mark i:nth-child(1) { height: 15px; }
        .mark i:nth-child(2) { height: 26px; background: var(--green-400); }
        .mark i:nth-child(3) { height: 20px; background: var(--green-600); }
        .aside .mark i { background: #fff; opacity: .55; }
        .aside .mark i:nth-child(2) { opacity: 1; }
        .brand-name { font-size: .95rem; font-weight: 800; letter-spacing: .02em; }
        .brand-name span { font-weight: 500; opacity: .7; }

        .hero h2 { max-width: 14ch; margin: 0; font-size: clamp(2.2rem, 3.6vw, 3.4rem); font-weight: 800; line-height: 1.06; letter-spacing: -.035em; }
        .hero p { max-width: 38ch; margin: 1.1rem 0 0; color: rgba(255,255,255,.72); font-size: 1rem; line-height: 1.65; }

        /* the one memorable thing: a live-feeling "today" stack */
        .stack { display: grid; gap: 10px; max-width: 360px; margin-top: 2.4rem; }
        .pill { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border: 1px solid rgba(255,255,255,.14); border-radius: 14px; background: rgba(255,255,255,.08); backdrop-filter: blur(8px); font-size: .82rem; }
        .pill:nth-child(2) { margin-left: 28px; }
        .pill:nth-child(3) { margin-left: 56px; }
        .pill b { display: block; font-weight: 700; }
        .pill small { color: rgba(255,255,255,.65); font-size: .74rem; }
        .dot { display: grid; place-items: center; width: 32px; height: 32px; flex: none; border-radius: 10px; background: rgba(255,255,255,.14); font-size: .9rem; }
        .aside-foot { color: rgba(255,255,255,.55); font-size: .78rem; }

        /* ---------- Form panel ---------- */
        .main { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2rem 1.25rem; }
        .panel { width: 100%; max-width: 440px; animation: rise .6s var(--ease) both; }
        @keyframes rise { from { opacity: 0; transform: translateY(14px); } }
        .m-brand { display: none; margin-bottom: 1.6rem; }
        .m-brand .brand-name { color: var(--ink); }
        .panel h1 { margin: 0; font-size: 1.85rem; font-weight: 800; letter-spacing: -.03em; }
        .panel .sub { margin: .45rem 0 1.8rem; color: var(--ink-3); font-size: .92rem; }

        .field { margin-bottom: 1.1rem; }
        .label { display: flex; align-items: center; justify-content: space-between; margin-bottom: .45rem; color: var(--ink); font-size: .82rem; font-weight: 600; }
        .label a { color: var(--green-700); font-weight: 600; font-size: .78rem; }
        .label a:hover { text-decoration: underline; }

        /* inputs */
        .control { position: relative; }
        .control > svg.lead { position: absolute; top: 50%; left: 14px; width: 18px; height: 18px; color: var(--ink-3); transform: translateY(-50%); pointer-events: none; transition: color .2s; }
        .control input { width: 100%; height: 50px; padding: 0 3rem 0 2.8rem; border: 1.5px solid var(--line); border-radius: 12px; outline: none; color: var(--ink); background: #fff; font-size: .92rem; transition: border-color .2s, box-shadow .2s; }
        .control input::placeholder { color: #a8aeb6; }
        .control input:focus { border-color: var(--green-700); box-shadow: 0 0 0 4px rgba(26,107,60,.12); }
        .control:focus-within > svg.lead { color: var(--green-700); }
        .control.invalid input { border-color: var(--danger); box-shadow: 0 0 0 4px rgba(192,57,43,.1); }
        .reveal { position: absolute; top: 50%; right: 8px; display: grid; place-items: center; width: 36px; height: 36px; border: 0; border-radius: 9px; color: var(--ink-3); background: transparent; cursor: pointer; transform: translateY(-50%); }
        .reveal:hover { color: var(--green-700); background: var(--green-50); }
        .reveal:focus-visible { outline: 3px solid rgba(26,107,60,.35); }
        .reveal svg { width: 18px; height: 18px; }
        .reveal .off { display: none; }
        .reveal[aria-pressed="true"] .on { display: none; }
        .reveal[aria-pressed="true"] .off { display: block; }
        .hint { display: none; align-items: center; gap: 6px; margin-top: .45rem; color: #9a6700; font-size: .76rem; font-weight: 500; }
        .hint.show { display: flex; }

        .remember { display: flex; align-items: center; gap: 9px; margin: .2rem 0 1.4rem; color: var(--ink-2); font-size: .84rem; cursor: pointer; user-select: none; }
        .remember input { width: 17px; height: 17px; margin: 0; accent-color: var(--green-700); }

        .btn { position: relative; display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; height: 52px; border: 0; border-radius: 12px; color: #fff; background: linear-gradient(180deg, #1f7a45, var(--green-700)); box-shadow: 0 8px 20px -8px rgba(26,107,60,.65), inset 0 1px 0 rgba(255,255,255,.18); font-size: .95rem; font-weight: 700; cursor: pointer; transition: transform .2s var(--ease), box-shadow .2s, filter .2s; }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 12px 24px -8px rgba(26,107,60,.75), inset 0 1px 0 rgba(255,255,255,.18); }
        .btn:active { transform: translateY(0); }
        .btn:focus-visible { outline: 3px solid rgba(26,107,60,.35); outline-offset: 3px; }
        .btn .arrow { width: 18px; height: 18px; transition: transform .25s var(--ease); }
        .btn:hover .arrow { transform: translateX(3px); }
        .btn .spin { display: none; width: 18px; height: 18px; border: 2.5px solid rgba(255,255,255,.35); border-top-color: #fff; border-radius: 50%; animation: spin .7s linear infinite; }
        .btn.loading { pointer-events: none; filter: saturate(.8) brightness(.95); }
        .btn.loading .spin { display: block; }
        .btn.loading .arrow { display: none; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .secure { display: flex; align-items: center; justify-content: center; gap: 7px; margin: 1.3rem 0 0; color: var(--ink-3); font-size: .76rem; }
        .secure svg { width: 14px; height: 14px; color: var(--green-700); }
        .foot { margin-top: 2rem; color: var(--ink-3); font-size: .74rem; text-align: center; }

        /* ---------- SweetAlert2 theme ---------- */
        .swal2-container { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
        .hr-popup { padding: 2rem 1.75rem 1.6rem !important; border-radius: 22px !important; box-shadow: 0 30px 80px -20px rgba(11,45,27,.45) !important; }
        .hr-title { color: var(--ink) !important; font-size: 1.3rem !important; font-weight: 800 !important; letter-spacing: -.02em; }
        .hr-text { color: var(--ink-2) !important; font-size: .92rem !important; line-height: 1.6 !important; }
        .hr-confirm, .hr-cancel { height: 44px; padding: 0 1.4rem; border: 0; border-radius: 11px; font-size: .88rem; font-weight: 700; cursor: pointer; transition: transform .15s, filter .2s; }
        .hr-confirm { color: #fff; background: var(--green-700); box-shadow: 0 8px 18px -8px rgba(26,107,60,.7); }
        .hr-cancel { margin-right: 8px; color: var(--ink-2); background: #eef1ef; }
        .hr-confirm:hover, .hr-cancel:hover { filter: brightness(.96); transform: translateY(-1px); }
        .hr-confirm:focus-visible, .hr-cancel:focus-visible { outline: 3px solid rgba(26,107,60,.35); outline-offset: 2px; }
        .hr-toast { border-radius: 14px !important; box-shadow: 0 14px 40px -12px rgba(0,0,0,.28) !important; }
        .swal2-icon.swal2-error { border-color: #f1c4bf !important; color: var(--danger) !important; }
        .swal2-icon.swal2-error [class^=swal2-x-mark-line] { background-color: var(--danger) !important; }
        .swal2-icon.swal2-success { border-color: var(--green-400) !important; color: var(--green-700) !important; }
        .swal2-icon.swal2-success [class^=swal2-success-line] { background-color: var(--green-700) !important; }
        .swal2-icon.swal2-warning { border-color: #f3d9a4 !important; color: #c48a12 !important; }
        .swal2-timer-progress-bar { background: var(--green-700) !important; }

        /* ---------- Responsive ---------- */
        @media (max-width: 980px) {
            .shell { grid-template-columns: 1fr; }
            .aside { display: none; }
            .m-brand { display: inline-flex; }
            .main { justify-content: flex-start; padding-top: 2.2rem; }
        }
        @media (max-width: 420px) {
            .panel h1 { font-size: 1.6rem; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation: none !important; transition: none !important; }
        }
    </style>
</head>
<body>
<div class="shell">
    <aside class="aside" aria-hidden="true">
        <a class="brand" href="{{ route('home') }}" tabindex="-1">
            <span class="mark"><i></i><i></i><i></i></span>
            <span class="brand-name">DEMO <span>HRMS</span></span>
        </a>

        <div class="hero">
            <h2>People work, sorted out.</h2>
            <p>Attendance, leave, payroll and onboarding live in one place, so your team spends less time on admin.</p>
            <div class="stack">
                <div class="pill"><span class="dot">✓</span><span><b>Leave approved</b><small>Ananya Rao · 2 days</small></span></div>
                <div class="pill"><span class="dot">₹</span><span><b>Payroll ready</b><small>October cycle · 148 people</small></span></div>
                <div class="pill"><span class="dot">★</span><span><b>New joiner today</b><small>Onboarding checklist started</small></span></div>
            </div>
        </div>

        <div class="aside-foot">&copy; {{ now()->year }} Demo HRMS Test &nbsp;&bull;&nbsp; Pune, India</div>
    </aside>

    <main class="main">
        <section class="panel" aria-labelledby="login-title">
            <a class="brand m-brand" href="{{ route('home') }}" aria-label="Demo HRMS home">
                <span class="mark" aria-hidden="true"><i></i><i></i><i></i></span>
                <span class="brand-name">DEMO <span>HRMS</span></span>
            </a>

            <h1 id="login-title">Welcome back</h1>
            <p class="sub">Sign in to your HRMS account</p>

            <form id="loginForm" method="post" action="{{ route('login.store') }}" novalidate>
                @csrf

                <div class="field">
                    <label class="label" for="email">Work email</label>
                    <div class="control" id="emailControl">
                        <svg class="lead" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="3"/><path d="M4 7.5l8 6 8-6"/></svg>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="you@company.com" autocomplete="username" inputmode="email" autocapitalize="off" spellcheck="false" required autofocus>
                    </div>
                </div>

                <div class="field">
                    <div class="label">
                        <label for="password">Password</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}">Forgot password?</a>
                        @else
                            <a href="#" id="forgotLink">Forgot password?</a>
                        @endif
                    </div>
                    <div class="control" id="passwordControl">
                        <svg class="lead" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4.5" y="10.5" width="15" height="10" rx="3"/><path d="M8 10.5V8a4 4 0 018 0v2.5"/></svg>
                        <input id="password" name="password" type="password" placeholder="Enter your password" autocomplete="current-password" required>
                        <button type="button" class="reveal" id="togglePassword" aria-label="Show password" aria-pressed="false">
                            <svg class="on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3l18 18"/><path d="M10.6 5.1A9.6 9.6 0 0112 5c6.4 0 10 7 10 7a17 17 0 01-3.2 4M6.5 6.6C3.7 8.4 2 12 2 12s3.6 7 10 7c1.5 0 2.8-.3 4-.8"/><path d="M9.9 9.9a3 3 0 004.2 4.2"/></svg>
                        </button>
                    </div>
                    <div class="hint" id="capsHint" role="status">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4l8 9h-5v6H9v-6H4z"/></svg>
                        Caps Lock is on
                    </div>
                </div>

                <label class="remember">
                    <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                    Keep me signed in on this device
                </label>

                <button type="submit" class="btn" id="submitBtn">
                    <span class="spin" aria-hidden="true"></span>
                    <span id="btnLabel">Sign in</span>
                    <svg class="arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </button>
            </form>

            <p class="secure">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l7 3v5c0 4.5-3 8-7 10-4-2-7-5.5-7-10V6l7-3z"/></svg>
                Encrypted connection. Demo workspace, use synthetic data only.
            </p>
            <p class="foot">&copy; {{ now()->year }} Demo HRMS Test</p>
        </section>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js" crossorigin="anonymous"></script>
<script>
(function () {
    'use strict';

    var form = document.getElementById('loginForm');
    var email = document.getElementById('email');
    var password = document.getElementById('password');
    var emailControl = document.getElementById('emailControl');
    var passwordControl = document.getElementById('passwordControl');
    var btn = document.getElementById('submitBtn');
    var btnLabel = document.getElementById('btnLabel');
    var caps = document.getElementById('capsHint');
    var toggle = document.getElementById('togglePassword');
    var forgot = document.getElementById('forgotLink');

    var themed = Swal.mixin({
        buttonsStyling: false,
        returnFocus: true,
        customClass: {
            popup: 'hr-popup',
            title: 'hr-title',
            htmlContainer: 'hr-text',
            confirmButton: 'hr-confirm',
            cancelButton: 'hr-cancel'
        }
    });

    var toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3800,
        timerProgressBar: true,
        customClass: { popup: 'hr-toast', title: 'hr-text' }
    });

    /* ---- Messages coming from the server ---- */
    var serverError = @json($errors->any() ? $errors->first() : null);
    var serverStatus = @json(session('status'));

    if (serverError) {
        themed.fire({
            icon: 'error',
            title: 'We couldn\u2019t sign you in',
            text: serverError,
            confirmButtonText: 'Try again'
        }).then(function () {
            password.value = '';
            (email.value ? password : email).focus();
        });
    } else if (serverStatus) {
        toast.fire({ icon: 'success', title: serverStatus });
    }

    /* ---- Show / hide password ---- */
    toggle.addEventListener('click', function () {
        var show = password.type === 'password';
        password.type = show ? 'text' : 'password';
        toggle.setAttribute('aria-pressed', String(show));
        toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        password.focus();
    });

    /* ---- Caps Lock hint ---- */
    function checkCaps(e) {
        if (typeof e.getModifierState === 'function') {
            caps.classList.toggle('show', e.getModifierState('CapsLock'));
        }
    }
    password.addEventListener('keydown', checkCaps);
    password.addEventListener('keyup', checkCaps);
    password.addEventListener('blur', function () { caps.classList.remove('show'); });

    /* ---- Clear invalid state while typing ---- */
    [[email, emailControl], [password, passwordControl]].forEach(function (pair) {
        pair[0].addEventListener('input', function () { pair[1].classList.remove('invalid'); });
    });

    /* ---- Client-side validation with friendly alerts ---- */
    function fail(control, input, title, text) {
        control.classList.add('invalid');
        themed.fire({ icon: 'warning', title: title, text: text, confirmButtonText: 'Got it' })
            .then(function () { input.focus(); });
    }

    form.addEventListener('submit', function (e) {
        var mail = email.value.trim();
        if (!mail) { e.preventDefault(); return fail(emailControl, email, 'Enter your work email', 'We need it to find your account.'); }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(mail)) { e.preventDefault(); return fail(emailControl, email, 'That email looks off', 'Check for typos, for example name@company.com.'); }
        if (!password.value) { e.preventDefault(); return fail(passwordControl, password, 'Enter your password', 'Your password is required to sign in.'); }

        email.value = mail;
        btn.classList.add('loading');
        btn.setAttribute('aria-busy', 'true');
        btnLabel.textContent = 'Signing you in\u2026';
    });

    /* Reset the button if the browser restores the page from back/forward cache */
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) {
            btn.classList.remove('loading');
            btn.removeAttribute('aria-busy');
            btnLabel.textContent = 'Sign in';
        }
    });

    /* ---- Forgot password fallback (when no password.request route exists) ---- */
    if (forgot) {
        forgot.addEventListener('click', function (e) {
            e.preventDefault();
            themed.fire({
                icon: 'info',
                title: 'Reset your password',
                text: 'Contact your HR team or system administrator and they will send you a reset link.',
                confirmButtonText: 'Okay'
            });
        });
    }
})();
</script>
</body>
</html>