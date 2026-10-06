@extends('layouts.portal')

@section('content')
@php
    $nonce = function_exists('csp_nonce') ? csp_nonce() : null;
@endphp

<style @if ($nonce) nonce="{{ $nonce }}" @endif>
    .account-page { max-width: 760px; margin: 0 auto; padding: 8px 4px 40px; color: #171717; }
    .account-heading { margin-bottom: 20px; }
    .account-eyebrow { margin: 0 0 6px; color: #15803d; font-size: 13px; font-weight: 700; }
    .account-heading h1 { margin: 0; font-size: clamp(28px, 4vw, 38px); line-height: 1.08; font-weight: 800; letter-spacing: -.03em; }
    .account-heading p:last-child { margin: 8px 0 0; color: #737373; }
    .account-card { padding: 24px; border: 1px solid #e8e8e8; border-radius: 14px; background: #fff; box-shadow: 0 1px 2px rgba(15,23,42,.04); }
    .account-card h2 { margin: 0; font-size: 19px; font-weight: 800; }
    .account-card > p { margin: 6px 0 20px; color: #737373; font-size: 13px; }
    .account-identity { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin-bottom: 22px; }
    .account-identity div { min-width: 0; padding: 12px 14px; border: 1px solid #e5e7eb; border-radius: 10px; background: #fafafa; }
    .account-identity small, .account-identity strong { display: block; overflow-wrap: anywhere; }
    .account-identity small { margin-bottom: 4px; color: #737373; font-size: 12px; }
    .account-form { display: grid; gap: 16px; max-width: 520px; }
    .account-form label { display: grid; gap: 6px; color: #404040; font-size: 13px; font-weight: 700; }
    .account-form input { width: 100%; min-height: 44px; padding: 0 13px; border: 1px solid #d1d5db; border-radius: 9px; color: #171717; background: #fff; font-family: inherit; font-size: 14px; }
    .account-form input:focus { border-color: #22c55e; box-shadow: 0 0 0 4px rgba(34,197,94,.14); outline: none; }
    .account-form input[aria-invalid="true"] { border-color: #ef4444; }
    .account-error { color: #b91c1c; font-size: 12px; font-weight: 600; }
    .account-notice { margin-bottom: 16px; padding: 12px 16px; border-radius: 10px; color: #166534; background: #f0fdf4; border: 1px solid #dcfce7; font-size: 14px; font-weight: 600; }
    .account-submit { justify-self: start; min-height: 42px; padding: 0 17px; border: 0; border-radius: 9px; color: #fff; background: #15803d; font: inherit; font-size: 14px; font-weight: 700; cursor: pointer; }
    .account-submit:hover { background: #166534; }
    @media (max-width: 560px) { .account-card { padding: 18px; } .account-identity { grid-template-columns: 1fr; } .account-submit { width: 100%; } }
</style>

<main class="account-page">
    <header class="account-heading">
        <p class="account-eyebrow">Account security</p>
        <h1>My account</h1>
        <p>Update your sign-in password. Your new password must be at least 8 characters.</p>
    </header>

    @if (session('status'))
        <div class="account-notice" role="status">{{ session('status') }}</div>
    @endif

    <section class="account-card">
        <h2>Change password</h2>
        <p>For your security, confirm your current password before saving a new one.</p>

        <div class="account-identity">
            <div><small>Name</small><strong>{{ $user->name }}</strong></div>
            <div><small>Sign-in email</small><strong>{{ $user->email }}</strong></div>
        </div>

        <form class="account-form" method="POST" action="{{ route('portal.account.password.update') }}">
            @csrf
            @method('PUT')
            <label for="current-password">Current password
                <input id="current-password" type="password" name="current_password" autocomplete="current-password" required aria-invalid="{{ $errors->has('current_password') ? 'true' : 'false' }}">
                @error('current_password')<span class="account-error">{{ $message }}</span>@enderror
            </label>
            <label for="new-password">New password
                <input id="new-password" type="password" name="password" minlength="8" maxlength="72" autocomplete="new-password" required aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}">
                @error('password')<span class="account-error">{{ $message }}</span>@enderror
            </label>
            <label for="confirm-password">Confirm new password
                <input id="confirm-password" type="password" name="password_confirmation" minlength="8" maxlength="72" autocomplete="new-password" required>
            </label>
            <button class="account-submit" type="submit">Update password</button>
        </form>
    </section>
</main>
@endsection
