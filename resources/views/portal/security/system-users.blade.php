@extends('layouts.portal')

@section('content')
@php
    $nonce = function_exists('csp_nonce') ? csp_nonce() : null;
@endphp

<style @if ($nonce) nonce="{{ $nonce }}" @endif>
    .su-page { max-width: 1280px; margin: 0 auto; padding: 8px 4px 40px; color: #171717; }
    .su-heading { display: flex; flex-wrap: wrap; align-items: end; justify-content: space-between; gap: 16px; margin-bottom: 22px; }
    .su-eyebrow { margin: 0 0 6px; color: #15803d; font-size: 13px; font-weight: 700; }
    .su-heading h1 { margin: 0; font-size: clamp(28px, 4vw, 40px); line-height: 1.05; font-weight: 800; letter-spacing: -.03em; }
    .su-heading p:last-child { margin: 8px 0 0; color: #737373; font-size: 15px; }
    .su-card { overflow: hidden; border: 1px solid #e8e8e8; border-radius: 14px; background: #fff; box-shadow: 0 1px 2px rgba(15,23,42,.04); }
    .su-notice { margin-bottom: 16px; padding: 12px 16px; border-radius: 10px; color: #166534; background: #f0fdf4; border: 1px solid #dcfce7; font-size: 14px; font-weight: 600; }
    .su-notice-error { color: #991b1b; background: #fef2f2; border-color: #fecaca; }
    .su-toolbar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px; padding: 20px 22px 16px; }
    .su-toolbar h2 { margin: 0; font-size: 19px; font-weight: 800; }
    .su-toolbar p { margin: 4px 0 0; color: #737373; font-size: 13px; }
    .su-tools { display: flex; flex: 1 1 520px; flex-wrap: nowrap; align-items: center; justify-content: flex-end; gap: 12px; }
    .su-sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; }
    .su-search { position: relative; display: block; flex: 1 1 auto; max-width: 420px; min-width: 180px; }
    .su-search svg { position: absolute; top: 50%; left: 13px; width: 17px; height: 17px; margin-top: -8.5px; color: #a3a3a3; pointer-events: none; fill: none; stroke: currentColor; stroke-width: 1.9; stroke-linecap: round; }
    .su-search input { width: 100%; min-height: 42px; padding: 0 14px 0 40px; border: 1px solid #e8e8e8; border-radius: 10px; color: #171717; background: #fff; font-family: inherit; font-size: 14px; font-weight: 500; line-height: 1.2; }
    .su-search input:focus { border-color: #22c55e; box-shadow: 0 0 0 4px rgba(34,197,94,.14); outline: none; }
    .su-tabs { display: inline-flex; flex: none; padding: 4px; border-radius: 12px; background: #f3f4f3; }
    .su-tabs a { display: inline-flex; align-items: center; gap: 8px; padding: 9px 14px; border-radius: 9px; color: #737373; font-size: 14px; font-weight: 700; text-decoration: none; }
    .su-tabs a.is-on { color: #166534; background: #fff; box-shadow: 0 2px 8px rgba(15,23,42,.1); }
    .su-tabs b { min-width: 24px; padding: 2px 7px; border-radius: 999px; color: #737373; background: #e8ebe9; font-size: 12px; text-align: center; }
    .su-tabs .is-on b { color: #fff; background: #15803d; }
    .su-scroll { overflow-x: auto; }
    #system-user-rows.is-loading { opacity: .45; pointer-events: none; }
    .su-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    .su-table th { padding: 11px 16px; border-block: 1px solid #e8e8e8; color: #737373; background: #fafafa; font-size: 12.5px; font-weight: 700; text-align: left; white-space: nowrap; }
    .su-table td { padding: 14px 16px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
    .su-table tr:last-child td { border-bottom: 0; }
    .su-user { display: flex; align-items: center; gap: 11px; min-width: 210px; }
    .su-avatar { display: grid; place-items: center; flex: none; width: 36px; height: 36px; border-radius: 11px; color: #166534; background: #f0fdf4; font-size: 13px; font-weight: 800; }
    .su-user strong, .su-user small, .su-cell strong, .su-cell small { display: block; }
    .su-user small, .su-cell small { margin-top: 3px; color: #737373; overflow-wrap: anywhere; }
    .su-cell { min-width: 120px; }
    .su-status { display: inline-flex; align-items: center; gap: 7px; padding: 4px 11px; border-radius: 999px; color: #166534; background: #f0fdf4; font-size: 12px; font-weight: 700; white-space: nowrap; }
    .su-status i { width: 7px; height: 7px; border-radius: 50%; background: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,.16); }
    .su-status.is-off { color: #737373; background: #f3f4f3; }
    .su-status.is-off i { background: #a3a3a3; box-shadow: none; }
    .su-actions { display: flex; align-items: center; justify-content: flex-end; gap: 8px; min-width: 230px; }
    .su-action { display: inline-flex; align-items: center; justify-content: center; min-height: 34px; padding: 7px 10px; border: 1px solid #e5e7eb; border-radius: 8px; color: #404040; background: #fff; font-family: inherit; font-size: 12px; font-weight: 600; line-height: 1.2; text-decoration: none; cursor: pointer; white-space: nowrap; }
    .su-action:hover { background: #f9fafb; }
    .su-action:disabled { opacity: .5; cursor: not-allowed; }
    .su-action-danger { color: #b91c1c; }
    .su-empty { padding: 44px 16px !important; color: #737373; text-align: center; }
    .su-footer { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 22px; color: #737373; font-size: 13px; }
    .su-footer nav { display: flex; gap: 8px; }
    .su-footer a, .su-footer span { padding: 6px 10px; border: 1px solid #e5e7eb; border-radius: 7px; color: #404040; text-decoration: none; }
    .su-page-button { padding: 6px 10px; border: 1px solid #e5e7eb; border-radius: 7px; color: #404040; background: #fff; font: inherit; cursor: pointer; }
    .su-page-button:disabled { color: #a3a3a3; background: #f9fafb; cursor: not-allowed; }
    .su-footer span[aria-current] { color: #fff; background: #15803d; border-color: #15803d; }
    .su-dialog { width: min(460px, calc(100vw - 28px)); padding: 22px; border: 1px solid #e5e7eb; border-radius: 16px; color: #171717; background: #fff; box-shadow: 0 24px 70px rgba(15,23,42,.25); }
    .su-dialog::backdrop { background: rgba(15,23,42,.42); backdrop-filter: blur(2px); }
    .su-dialog h2 { margin: 0; font-size: 20px; }
    .su-dialog p { margin: 6px 0 18px; color: #737373; font-size: 13px; }
    .su-dialog label { display: grid; gap: 6px; margin-top: 12px; color: #404040; font-size: 13px; font-weight: 700; }
    .su-dialog input { width: 100%; min-height: 42px; padding: 0 12px; border: 1px solid #d1d5db; border-radius: 9px; font-family: inherit; font-size: 14px; font-weight: 500; line-height: 1.2; }
    .su-dialog input:focus { border-color: #22c55e; box-shadow: 0 0 0 4px rgba(34,197,94,.14); outline: none; }
    .su-dialog-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 20px; }
    .su-dialog-error { color: #b91c1c; font-size: 12px; }
    @media (max-width: 900px) { .su-toolbar { align-items: flex-start; flex-direction: column; } .su-tools { width: 100%; justify-content: flex-start; } }
    @media (max-width: 560px) { .su-tools { flex-wrap: wrap; } .su-search { flex-basis: 100%; max-width: none; } .su-tabs { width: 100%; } .su-tabs a { flex: 1; justify-content: center; } .su-actions { justify-content: flex-start; } }
</style>

<main class="su-page">
    <header class="su-heading">
        <div>
            <p class="su-eyebrow">Super admin · Security</p>
            <h1>System users</h1>
            <p>View user accounts, manage access status, and reset account passwords.</p>
        </div>
    </header>

    @if (session('status'))
        <div class="su-notice" role="status">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="su-notice su-notice-error" role="alert">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif
    <div class="su-notice" role="status" data-ajax-notice hidden></div>

    <section class="su-card" id="system-users-panel" data-url="{{ route('portal.system-users.data') }}" data-current-page="{{ $users->currentPage() }}">
        <div class="su-toolbar">
            <div>
                <h2>{{ ucfirst($selectedStatus) }} accounts</h2>
                <p><span data-user-count>{{ number_format($users->total()) }}</span> user accounts in this view</p>
            </div>
            <div class="su-tools">
                <form class="su-search" method="GET" action="{{ route('portal.system-users.index') }}" data-user-search-form>
                    <input type="hidden" name="status" value="{{ $selectedStatus }}" data-user-status-input>
                    <label class="su-sr" for="system-user-search">Search system users</label>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="M16 16l4 4"/></svg>
                    <input id="system-user-search" type="search" name="search" value="{{ $search }}" placeholder="Search name, email, role or employee ID" autocomplete="off" data-user-search-input>
                </form>
                <nav class="su-tabs" aria-label="Filter users by status">
                    <a class="{{ $selectedStatus === 'active' ? 'is-on' : '' }}" href="{{ route('portal.system-users.index', ['status' => 'active', 'search' => $search]) }}" data-user-status="active" @if ($selectedStatus === 'active') aria-current="page" @endif>Active <b data-active-user-count>{{ $activeUserCount }}</b></a>
                    <a class="{{ $selectedStatus === 'inactive' ? 'is-on' : '' }}" href="{{ route('portal.system-users.index', ['status' => 'inactive', 'search' => $search]) }}" data-user-status="inactive" @if ($selectedStatus === 'inactive') aria-current="page" @endif>Inactive <b data-inactive-user-count>{{ $inactiveUserCount }}</b></a>
                </nav>
            </div>
        </div>

        <div class="su-scroll">
            <table class="su-table">
                <thead>
                    <tr><th>User</th><th>Role</th><th>Employee record</th><th>Email verification</th><th>Account status</th><th>Created</th><th>Last updated</th><th>Actions</th></tr>
                </thead>
                <tbody id="system-user-rows" aria-live="polite">
                    @include('portal.security.system-user-rows', ['users' => $users, 'selectedStatus' => $selectedStatus])
                </tbody>
            </table>
        </div>

        <footer class="su-footer">
            <span data-user-summary>Showing {{ $users->firstItem() ?: 0 }}–{{ $users->lastItem() ?: 0 }} of {{ number_format($users->total()) }} accounts</span>
            <nav aria-label="User list pagination">
                <button class="su-page-button" type="button" data-user-prev {{ $users->onFirstPage() ? 'disabled' : '' }}>Previous</button>
                <span data-user-page>Page {{ $users->currentPage() }} of {{ $users->lastPage() }}</span>
                <button class="su-page-button" type="button" data-user-next {{ $users->currentPage() >= $users->lastPage() ? 'disabled' : '' }}>Next</button>
            </nav>
        </footer>
    </section>
</main>

<dialog class="su-dialog" id="system-user-password-dialog" aria-labelledby="system-user-password-title">
    <form method="POST" id="system-user-password-form" action="{{ $passwordUrl }}">
        @csrf
        <h2 id="system-user-password-title">Change user password</h2>
        <p>Set a new password for <strong data-password-target-name></strong>. Passwords must be at least 8 characters.</p>
        <label for="system-user-password">New password
            <input id="system-user-password" type="password" name="password" minlength="8" maxlength="72" autocomplete="new-password" required>
        </label>
        <label for="system-user-password-confirmation">Confirm new password
            <input id="system-user-password-confirmation" type="password" name="password_confirmation" minlength="8" maxlength="72" autocomplete="new-password" required>
        </label>
        <small class="su-dialog-error" data-password-error></small>
        <div class="su-dialog-actions">
            <button class="su-action" type="button" data-password-cancel>Cancel</button>
            <button class="su-action" type="submit">Save password</button>
        </div>
    </form>
</dialog>

<script @if ($nonce) nonce="{{ $nonce }}" @endif>
    (function () {
        var panel = document.getElementById('system-users-panel');
        var rows = document.getElementById('system-user-rows');
        var form = document.querySelector('[data-user-search-form]');
        var search = form.querySelector('[data-user-search-input]');
        var statusInput = form.querySelector('[data-user-status-input]');
        var previous = document.querySelector('[data-user-prev]');
        var next = document.querySelector('[data-user-next]');
        var pageLabel = document.querySelector('[data-user-page]');
        var summary = document.querySelector('[data-user-summary]');
        var totalLabel = document.querySelector('[data-user-count]');
        var notice = document.querySelector('[data-ajax-notice]');
        var selectedPage = parseInt(panel.getAttribute('data-current-page'), 10) || 1;
        var requestId = 0;
        var searchTimer = null;
        var dialog = document.getElementById('system-user-password-dialog');
        var passwordForm = document.getElementById('system-user-password-form');
        var password = document.getElementById('system-user-password');
        var confirmation = document.getElementById('system-user-password-confirmation');
        var error = dialog.querySelector('[data-password-error]');
        var templateUrl = @json($passwordUrl);

        function notify(message, failed) {
            notice.textContent = message;
            notice.classList.toggle('su-notice-error', !!failed);
            notice.hidden = !message;
        }

        function updateUrl(usePushState) {
            var query = new URLSearchParams();
            query.set('status', statusInput.value);
            if (search.value.trim()) query.set('search', search.value.trim());
            query.set('page', selectedPage);
            var url = window.location.pathname + '?' + query.toString();
            if (usePushState) window.history.pushState(null, '', url);
            else window.history.replaceState(null, '', url);
        }

        function loadUsers(page, usePushState) {
            selectedPage = Math.max(1, page || 1);
            panel.setAttribute('data-current-page', selectedPage);
            rows.classList.add('is-loading');
            var currentRequest = ++requestId;
            var query = new URLSearchParams({
                status: statusInput.value,
                search: search.value.trim(),
                page: selectedPage
            });

            fetch(panel.getAttribute('data-url') + '?' + query.toString(), {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            }).then(function (response) {
                return response.json().then(function (payload) {
                    if (!response.ok) throw new Error(payload.message || 'Could not load system users.');
                    return payload;
                });
            }).then(function (payload) {
                if (currentRequest !== requestId) return;
                rows.innerHTML = payload.html;
                selectedPage = payload.meta.current_page;
                panel.setAttribute('data-current-page', selectedPage);
                pageLabel.textContent = 'Page ' + payload.meta.current_page + ' of ' + payload.meta.last_page;
                summary.textContent = 'Showing ' + (payload.meta.from || 0) + '–' + (payload.meta.to || 0) +
                    ' of ' + Number(payload.meta.total).toLocaleString() + ' accounts';
                totalLabel.textContent = Number(payload.meta.total).toLocaleString();
                previous.disabled = payload.meta.current_page <= 1;
                next.disabled = payload.meta.current_page >= payload.meta.last_page;
                document.querySelector('[data-active-user-count]').textContent = payload.counts.active;
                document.querySelector('[data-inactive-user-count]').textContent = payload.counts.inactive;
                document.querySelectorAll('[data-user-status]').forEach(function (link) {
                    var active = link.getAttribute('data-user-status') === statusInput.value;
                    link.classList.toggle('is-on', active);
                    if (active) link.setAttribute('aria-current', 'page');
                    else link.removeAttribute('aria-current');
                });
                document.querySelector('.su-toolbar h2').textContent =
                    statusInput.value.charAt(0).toUpperCase() + statusInput.value.slice(1) + ' accounts';
                rows.classList.remove('is-loading');
                updateUrl(!!usePushState);
            }).catch(function (fetchError) {
                if (currentRequest !== requestId) return;
                rows.classList.remove('is-loading');
                notify(fetchError.message || 'Could not load system users. Please try again.', true);
            });
        }

        function responsePayload(response) {
            return response.json().then(function (payload) {
                if (!response.ok) {
                    var validationMessage = payload.errors
                        ? Object.keys(payload.errors).map(function (key) { return payload.errors[key][0]; })[0]
                        : '';
                    throw new Error(validationMessage || payload.message || 'The request could not be completed.');
                }
                return payload;
            });
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            clearTimeout(searchTimer);
            loadUsers(1, false);
        });
        search.addEventListener('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () { loadUsers(1, false); }, 250);
        });
        document.querySelectorAll('[data-user-status]').forEach(function (link) {
            link.addEventListener('click', function (event) {
                event.preventDefault();
                statusInput.value = link.getAttribute('data-user-status');
                loadUsers(1, true);
            });
        });
        previous.addEventListener('click', function () { loadUsers(selectedPage - 1, true); });
        next.addEventListener('click', function () { loadUsers(selectedPage + 1, true); });
        window.addEventListener('popstate', function () {
            var query = new URLSearchParams(window.location.search);
            statusInput.value = query.get('status') === 'inactive' ? 'inactive' : 'active';
            search.value = query.get('search') || '';
            loadUsers(parseInt(query.get('page'), 10) || 1, false);
        });

        rows.addEventListener('click', function (event) {
            var button = event.target.closest('[data-password-user]');
            if (!button) return;
            passwordForm.action = templateUrl.replace('__USER__', encodeURIComponent(button.getAttribute('data-password-user')));
            dialog.querySelector('[data-password-target-name]').textContent = button.getAttribute('data-password-name');
            passwordForm.reset();
            error.textContent = '';
            dialog.showModal();
            password.focus();
        });
        dialog.querySelector('[data-password-cancel]').addEventListener('click', function () {
            dialog.close();
        });
        passwordForm.addEventListener('submit', function (event) {
            error.textContent = '';
            if (password.value !== confirmation.value) {
                event.preventDefault();
                error.textContent = 'The password confirmation does not match.';
                confirmation.focus();
                return;
            }
            event.preventDefault();
            fetch(passwordForm.action, {
                method: 'POST',
                body: new FormData(passwordForm),
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            }).then(responsePayload).then(function (payload) {
                dialog.close();
                notify(payload.message, false);
                loadUsers(selectedPage);
            }).catch(function (submitError) {
                error.textContent = submitError.message || 'Could not change the password.';
            });
        });
        rows.addEventListener('submit', function (event) {
            var statusForm = event.target.closest('form[action*="/system-users/"][action*="/status"]');
            if (!statusForm) return;
            event.preventDefault();
            fetch(statusForm.action, {
                method: 'POST',
                body: new FormData(statusForm),
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            }).then(responsePayload).then(function (payload) {
                notify(payload.message, false);
                var status = new FormData(statusForm).get('is_active') === '1' ? 'active' : 'inactive';
                statusInput.value = status;
                loadUsers(1, true);
            }).catch(function (submitError) {
                notify(submitError.message || 'Could not update the user account.', true);
            });
        });
        dialog.addEventListener('click', function (event) {
            if (event.target === dialog) dialog.close();
        });
    }());
</script>
@endsection
