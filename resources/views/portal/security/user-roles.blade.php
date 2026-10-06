@extends('layouts.portal')

@section('content')
@php
    $user    = auth()->user();
    $isSuper = $user->isSuperAdmin();
    $nonce   = function_exists('csp_nonce') ? csp_nonce() : null;
    $can     = [];
    foreach (['create', 'edit', 'delete'] as $action) {
        $can[$action] = $user->hasPermission('user_roles', $action);
    }
    $activeN   = (int) $activeRoleCount;
    $inactiveN = (int) $inactiveRoleCount;
    $allN      = $activeN + $inactiveN;
    $share     = $allN > 0 ? round($activeN / $allN * 100) : 0;
    $eyebrow   = ($isSuper ? 'Super admin' : ucwords(str_replace('_', ' ', $user->role))) . ' · Security';
    $systemRoles = ['admin', 'super_admin'];
    $hasAccess = \Illuminate\Support\Facades\Route::has('portal.module-access.index');
    $userTotal = (int) $roles->sum('users_count');
@endphp

{{-- Icon sprite: one definition shared by server-rendered and JS-rendered rows --}}
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <symbol id="dp-i-view" viewBox="0 0 24 24"><path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12z"/><circle cx="12" cy="12" r="2.8"/></symbol>
    <symbol id="dp-i-edit" viewBox="0 0 24 24"><path d="M4 20h4L19.5 8.5a2.1 2.1 0 00-3-3L5 17v3z"/><path d="M14.5 7.5l3 3"/></symbol>
    <symbol id="dp-i-off" viewBox="0 0 24 24"><path d="M12 3v8"/><path d="M6.4 6.8a8 8 0 1011.2 0"/></symbol>
    <symbol id="dp-i-on" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.7 2.7L16 9.5"/></symbol>
    <symbol id="dp-i-shield" viewBox="0 0 24 24"><path d="M12 3l7 3v5c0 4.5-3 8-7 10-4-2-7-5.5-7-10V6l7-3z"/><path d="M9.5 12l2 2 3.5-4"/></symbol>
</svg>

<style @if ($nonce) nonce="{{ $nonce }}" @endif>
.dp, .dp-modal {
    --g: var(--green, #15803d); --g-d: var(--green-dark, #166534); --g-l: var(--green-bg, #f0fdf4); --g-b: var(--green-border, #dcfce7);
    --ink: #171717; --mut: #737373; --line: var(--border, #e8e8e8); --soft: #fafafa;
}
.dp {
    max-width: 1280px; margin: 0 auto; padding: 8px 4px 40px; color: var(--ink);
    font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif; -webkit-font-smoothing: antialiased;
}
.dp *, .dp-modal *, .dp *::before { box-sizing: border-box; }
.dp svg, .dp-modal svg, .dp-ico { fill: none; stroke: currentColor; stroke-width: 1.9; stroke-linecap: round; stroke-linejoin: round; }
.dp-sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); }

.dp-in { opacity: 0; transform: translateY(14px); animation: dp-rise .6s cubic-bezier(.22,1,.36,1) forwards; animation-delay: calc(var(--i) * 80ms); }
@keyframes dp-rise { to { opacity: 1; transform: none; } }

/* Heading + buttons */
.dp-head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 18px; margin-bottom: 22px; }
.dp-eyebrow { margin: 0 0 6px; color: var(--g); font-size: 13px; font-weight: 700; }
.dp-head h1 { margin: 0; font-size: clamp(28px, 4vw, 40px); line-height: 1.05; font-weight: 800; letter-spacing: -.03em; }
.dp-sub { margin: 8px 0 0; color: var(--mut); font-size: 15px; }
.dp-btn {
    display: inline-flex; align-items: center; gap: 8px; min-height: 42px; padding: 0 18px; border-radius: 10px;
    border: 1px solid transparent; font: 700 14px/1 inherit; font-family: inherit; cursor: pointer; text-decoration: none;
    transition: transform .15s, background .15s, box-shadow .15s;
}
.dp-btn svg { width: 17px; height: 17px; }
.dp-btn:active:not(:disabled) { transform: scale(.97); }
.dp-btn:disabled { opacity: .5; cursor: not-allowed; }
.dp-solid { color: #fff; background: var(--g); box-shadow: 0 6px 16px rgba(21,128,61,.22); }
.dp-solid:hover:not(:disabled) { background: var(--g-d); }
.dp-ghost { color: var(--ink); background: #fff; border-color: var(--line); }
.dp-ghost:hover:not(:disabled) { background: var(--soft); }
.dp-sm { min-height: 36px; padding: 0 14px; font-size: 13px; }
.dp a:focus-visible, .dp button:focus-visible, .dp input:focus-visible, .dp-modal :focus-visible { outline: 2px solid #22c55e; outline-offset: 2px; }

.dp-card { background: #fff; border: 1px solid var(--line); border-radius: 14px; box-shadow: 0 1px 2px rgba(15,23,42,.04); }
.dp-notice { margin-bottom: 16px; padding: 12px 16px; border-radius: 11px; font-size: 14px; font-weight: 600; animation: dp-rise .35s both; }
.dp-notice.ok { color: var(--g-d); background: var(--g-l); border: 1px solid var(--g-b); }
.dp-notice.bad { color: #991b1b; background: #fef2f2; border: 1px solid #fecaca; }

/* Overview: segmented switch and active share graph */
.dp-overview { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 20px; margin-bottom: 16px; padding: 16px 20px; }
.dp-switch { display: inline-flex; padding: 4px; border-radius: 12px; background: #f3f4f3; }
.dp-switch a { display: inline-flex; align-items: center; gap: 8px; padding: 9px 16px; border-radius: 9px; color: var(--mut); font-size: 14px; font-weight: 700; text-decoration: none; transition: background .2s, color .2s, box-shadow .2s; }
.dp-switch a:hover { color: var(--ink); }
.dp-switch a.is-on { color: var(--g-d); background: #fff; box-shadow: 0 2px 8px rgba(15,23,42,.1); }
.dp-switch b { min-width: 24px; padding: 2px 8px; border-radius: 999px; color: var(--mut); background: #e8ebe9; font-size: 12px; text-align: center; font-variant-numeric: tabular-nums; }
.dp-switch .is-on b { color: #fff; background: var(--g); }
.dp-ratio { flex: 1; min-width: 220px; max-width: 420px; }
.dp-ratio-top { display: flex; justify-content: space-between; gap: 10px; font-size: 13px; color: var(--mut); }
.dp-ratio-top strong { color: var(--ink); font-size: 15px; font-weight: 800; }
.dp-rail { height: 10px; margin-top: 8px; overflow: hidden; border-radius: 999px; background: #eceeed; }
.dp-rail i { display: block; width: var(--w); height: 100%; border-radius: inherit; background: linear-gradient(90deg, #22c55e, #15803d); transform-origin: left; transition: width .6s cubic-bezier(.22,1,.36,1); animation: dp-grow 1s cubic-bezier(.22,1,.36,1) .4s both; }
@keyframes dp-grow { from { transform: scaleX(0); } }

/* List panel */
.dp-ph { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px; padding: 20px 22px 16px; }
.dp-ph h2 { margin: 0; font-size: 19px; font-weight: 800; letter-spacing: -.02em; }
.dp-ph p { margin: 4px 0 0; color: var(--mut); font-size: 13px; }
.dp-search { position: relative; display: block; width: min(320px, 100%); }
.dp-search svg { position: absolute; left: 13px; top: 50%; width: 17px; height: 17px; margin-top: -8.5px; color: #a3a3a3; pointer-events: none; }
.dp-search input, .dp-grid input {
    width: 100%; min-height: 42px; border: 1px solid var(--line); border-radius: 10px; color: var(--ink); background: #fff;
    font: 500 14px inherit; font-family: inherit; transition: border-color .15s, box-shadow .15s;
}
.dp-search input { padding: 0 14px 0 40px; }
.dp-search input:focus, .dp-grid input:focus { border-color: #22c55e; box-shadow: 0 0 0 4px rgba(34,197,94,.14); outline: none; }

.dp-scroll { overflow-x: auto; }
.dp table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
.dp th { padding: 11px 16px; border-block: 1px solid var(--line); color: var(--mut); background: var(--soft); font-size: 12.5px; font-weight: 700; text-align: left; white-space: nowrap; }
.dp td { padding: 13px 16px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
.dp tbody tr { transition: background .15s; }
.dp tbody tr:hover { background: #fafcfa; }
.dp tbody.is-loading { opacity: .45; pointer-events: none; transition: opacity .15s; }
.dp-empty { padding: 44px 16px !important; color: var(--mut); text-align: center; }
.dp-code { padding: 4px 9px; border-radius: 7px; color: var(--g-d); background: var(--g-l); border: 1px solid var(--g-b); font: 700 12px ui-monospace, SFMono-Regular, Menlo, monospace; }
.dp-name { display: flex; align-items: center; gap: 11px; min-width: 150px; }
.dp-name strong { font-weight: 700; }
.dp-av { display: grid; place-items: center; flex: none; width: 34px; height: 34px; border-radius: 10px; font-size: 13px; font-weight: 800; }
.dp-a0 { color: #2563eb; background: #eff6ff; } .dp-a1 { color: #7c3aed; background: #f5f3ff; } .dp-a2 { color: #ea580c; background: #fff7ed; }
.dp-a3 { color: #15803d; background: #f0fdf4; } .dp-a4 { color: #0891b2; background: #ecfeff; } .dp-a5 { color: #4f46e5; background: #eef2ff; }
.dp-status { display: inline-flex; align-items: center; gap: 7px; padding: 4px 11px; border-radius: 999px; color: #737373; background: #f3f4f3; font-size: 12px; font-weight: 700; }
.dp-status i { width: 7px; height: 7px; border-radius: 50%; background: #a3a3a3; }
.dp-status.is-active { color: var(--g-d); background: var(--g-l); }
.dp-status.is-active i { background: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,.18); }
.dp-actions { white-space: nowrap; text-align: right; }
.dp-act { display: inline-grid; place-items: center; width: 34px; height: 34px; padding: 0; border: 1px solid var(--line); border-radius: 9px; color: #525252; background: #fff; cursor: pointer; transition: color .15s, background .15s, border-color .15s, transform .15s; }
.dp-act + .dp-act { margin-left: 6px; }
.dp-ico { width: 17px; height: 17px; pointer-events: none; }
.dp-act:active:not(:disabled) { transform: scale(.92); }
.dp-act.is-view:hover { color: #2563eb; background: #eff6ff; border-color: #bfdbfe; }
.dp-act.is-edit:hover, .dp-act.is-on:hover { color: var(--g-d); background: var(--g-l); border-color: var(--g-b); }
.dp-act.is-off:hover { color: #b91c1c; background: #fef2f2; border-color: #fecaca; }
.dp-act:disabled { opacity: .45; cursor: wait; }
.dp-foot { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 22px; color: var(--mut); font-size: 13px; }
.dp-pager { display: flex; align-items: center; gap: 12px; }

/* Modals (native dialog: focus trap and Escape come built in) */
.dp-modal { width: min(640px, calc(100vw - 24px)); max-height: calc(100dvh - 24px); padding: 24px; overflow-y: auto; border: 1px solid var(--line); border-radius: 18px; color: #171717; background: #fff; box-shadow: 0 30px 80px rgba(15,23,42,.28); font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
.dp-modal[open] { animation: dp-pop .28s cubic-bezier(.22,1,.36,1); }
.dp-modal::backdrop { background: rgba(15,23,42,.42); backdrop-filter: blur(3px); }
@keyframes dp-pop { from { opacity: 0; transform: translateY(14px) scale(.97); } }
.dp-mhead { display: flex; justify-content: space-between; gap: 16px; margin-bottom: 20px; }
.dp-mhead h2 { margin: 0; font-size: 22px; font-weight: 800; letter-spacing: -.02em; }
.dp-mhead p { margin: 4px 0 0; color: var(--mut); font-size: 13.5px; }
.dp-mhead .dp-eyebrow { margin: 0 0 4px; color: var(--g); font-size: 13px; }
.dp-x { width: 34px; height: 34px; flex: none; border: 1px solid var(--line); border-radius: 9px; color: #525252; background: #fff; font-size: 20px; line-height: 1; cursor: pointer; }
.dp-x:hover { color: var(--g); background: var(--g-l); }
.dp-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; }
.dp-grid label { display: grid; gap: 6px; color: #404040; font-size: 13px; font-weight: 700; }
.dp-grid input { padding: 0 13px; }
.dp-grid input::placeholder { color: #a3a3a3; font-weight: 500; }
.dp-grid input[aria-invalid="true"] { border-color: #ef4444; box-shadow: 0 0 0 4px rgba(239,68,68,.12); }
.dp-fe { color: #b91c1c; font-size: 12px; font-weight: 600; line-height: 1.3; }
.dp-fe:empty { display: none; }
.dp-error { margin-top: 14px; padding: 11px 14px; border-radius: 10px; color: #991b1b; background: #fef2f2; border: 1px solid #fecaca; font-size: 13.5px; font-weight: 600; }
.dp-mfoot { display: flex; justify-content: flex-end; gap: 10px; margin-top: 22px; }
.dp-details { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin: 0; }
.dp-details div { padding: 12px 14px; border: 1px solid var(--line); border-radius: 11px; background: var(--soft); }
.dp-details dt { color: var(--mut); font-size: 12px; font-weight: 600; }
.dp-details dd { margin: 3px 0 0; font-size: 14.5px; font-weight: 700; overflow-wrap: anywhere; }

@media (max-width: 1180px) { .dp-lg { display: none; } }
@media (max-width: 860px) { .dp-md { display: none; } }
@media (max-width: 560px) {
    .dp-grid, .dp-details { grid-template-columns: 1fr; }
    .dp-head .dp-btn { width: 100%; justify-content: center; }
    .dp-ph, .dp-foot { padding-inline: 16px; }
}
@media (prefers-reduced-motion: reduce) { .dp *, .dp-modal, .dp-in { animation: none !important; transition: none !important; opacity: 1; transform: none; } }
</style>

<div class="dp">

    <header class="dp-head dp-in" style="--i:0">
        <div>
            <p class="dp-eyebrow">{{ $eyebrow }}</p>
            <h1>User roles</h1>
            <p class="dp-sub">Manage roles, their availability and who they apply to across the organisation.</p>
        </div>
        @if ($hasAccess)
            <nav class="dp-switch dp-in" style="--i:1" aria-label="Security navigation">
                <a class="is-on" href="{{ route('portal.user-roles.index') }}" aria-current="page">User roles</a>
                <a href="{{ route('portal.module-access.index') }}">Module access</a>
            </nav>
        @endif
    </header>

    @if ($errors->any())
        <div class="dp-error" role="alert" style="margin-bottom:16px">{{ $errors->first() }}</div>
    @endif

    {{-- Overview --}}
    <section class="ur-stats" aria-label="Role overview">
        <article class="dp-card ur-stat dp-in" style="--i:1">
            <span class="dp-av dp-a1"><svg class="dp-ico" viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="7" width="17" height="12" rx="3"/><path d="M9 7V5.5A1.5 1.5 0 0110.5 4h3A1.5 1.5 0 0115 5.5V7"/><path d="M3.5 12.5h17"/></svg></span>
            <p>Total roles</p><strong data-count="{{ $allN }}">{{ $allN }}</strong><small>All configured roles</small>
        </article>
        <article class="dp-card ur-stat dp-in" style="--i:2">
            <span class="dp-av dp-a3"><svg class="dp-ico" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.7 2.7L16 9.5"/></svg></span>
            <p>Active roles</p><strong data-count="{{ $activeN }}">{{ $activeN }}</strong><small>{{ $share }}% of all roles</small>
            <div class="dp-rail ur-share" style="--w: {{ $share }}%"><i></i></div>
        </article>
        <article class="dp-card ur-stat dp-in" style="--i:3">
            <span class="dp-av dp-a2"><svg class="dp-ico" viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3.2"/><path d="M3 19c.6-3.2 3-5 6-5s5.4 1.8 6 5"/><path d="M16 5.2a3 3 0 010 5.6"/><path d="M18 14.3c1.7.6 2.7 2.2 3 4.7"/></svg></span>
            <p>Users in {{ $selectedStatus }} roles</p><strong data-count="{{ $userTotal }}">{{ $userTotal }}</strong><small>Accounts assigned to the roles below</small>
        </article>
    </section>

    {{-- Add role --}}
    @if ($can['create'])
        <section class="dp-card ur-create dp-in" style="--i:4">
            <div>
                <h2>Add new role</h2>
                <p>Create a custom role. Module access can be set after it is created.</p>
            </div>
            <form method="POST" action="{{ route('portal.user-roles.store') }}" class="ur-add" data-ur-form novalidate>
                @csrf
                <div class="ur-f">
                    <label for="ur-name">Role name</label>
                    <input id="ur-name" class="ur-input" type="text" name="name" maxlength="30" required autocomplete="off" spellcheck="false" autocapitalize="none"
                           placeholder="e.g. hr_manager" aria-describedby="ur-help ur-err" data-ur-name>
                    <small class="dp-fe" id="ur-err" role="alert"></small>
                    <small class="ur-help" id="ur-help">2–30 characters: lowercase letters, numbers and underscores.</small>
                </div>
                <button class="dp-btn dp-solid" type="submit">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>Add role
                </button>
            </form>
        </section>
    @endif

    {{-- Role list --}}
    <section class="dp-card dp-in" style="--i:5">
        <div class="dp-ph ur-ph">
            <div>
                <h2>{{ ucfirst($selectedStatus) }} roles</h2>
                <p>Showing <span data-ur-count>{{ $roles->count() }}</span> of {{ $roles->count() }} {{ $roles->count() === 1 ? 'role' : 'roles' }}</p>
            </div>
            <div class="ur-tools">
                <label class="dp-search" for="ur-search">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="M16 16l4 4"/></svg>
                    <input id="ur-search" type="search" placeholder="Search roles" aria-label="Search roles" autocomplete="off">
                </label>
                <div class="dp-switch" role="group" aria-label="Filter roles by status">
                    <a class="{{ $selectedStatus === 'active' ? 'is-on' : '' }}" href="{{ route('portal.user-roles.index', ['status' => 'active']) }}" @if ($selectedStatus === 'active') aria-current="true" @endif>Active <b>{{ $activeN }}</b></a>
                    <a class="{{ $selectedStatus === 'inactive' ? 'is-on' : '' }}" href="{{ route('portal.user-roles.index', ['status' => 'inactive']) }}" @if ($selectedStatus === 'inactive') aria-current="true" @endif>Inactive <b>{{ $inactiveN }}</b></a>
                </div>
            </div>
        </div>

        <div class="dp-scroll">
            <table>
                <thead><tr><th>Role</th><th>Assigned users</th><th>Status</th><th><span class="dp-sr">Actions</span></th></tr></thead>
                <tbody id="ur-rows">
                    @forelse ($roles as $role)
                        @php
                            $system  = in_array($role->name, $systemRoles, true);
                            $display = ucwords(str_replace('_', ' ', $role->name));
                        @endphp
                        <tr data-ur-row data-role="{{ strtolower($role->name) }}">
                            <td>
                                <div class="dp-name">
                                    <span class="dp-av dp-a{{ $loop->index % 6 }}">{{ mb_strtoupper(mb_substr($role->name, 0, 1)) }}</span>
                                    <div class="ur-role">
                                        <strong>{{ $display }}</strong>
                                        <code>{{ $role->name }}</code>
                                    </div>
                                    @if ($system)<span class="ur-sys">System</span>@endif
                                </div>
                            </td>
                            <td>
                                <div class="ur-users"><b>{{ (int) $role->users_count }}</b></div>
                            </td>
                            <td><span class="dp-status {{ $role->is_active ? 'is-active' : '' }}"><i></i>{{ $role->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="dp-actions">
                                @if ($system)
                                    <span class="ur-prot" title="System roles can’t be edited or deactivated">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="11" width="14" height="9" rx="2.5"/><path d="M8 11V8a4 4 0 018 0v3"/></svg>Protected
                                    </span>
                                @else
                                    @if ($can['edit'])
                                        <button class="dp-act is-edit" type="button" title="Edit role" aria-label="Edit {{ $display }}" data-ur-edit data-id="{{ $role->id }}" data-name="{{ $role->name }}"><svg class="dp-ico" aria-hidden="true"><use href="#dp-i-edit"/></svg></button>
                                    @endif
                                    @if ($can['delete'])
                                        <form method="POST" action="{{ route('portal.user-roles.status', $role) }}" class="ur-inline" data-ur-status data-name="{{ $display }}" data-users="{{ (int) $role->users_count }}" data-next="{{ $role->is_active ? 0 : 1 }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="is_active" value="{{ $role->is_active ? 0 : 1 }}">
                                            <button class="dp-act {{ $role->is_active ? 'is-off' : 'is-on' }}" type="submit" title="{{ $role->is_active ? 'Deactivate' : 'Activate' }} role" aria-label="{{ $role->is_active ? 'Deactivate' : 'Activate' }} {{ $display }}"><svg class="dp-ico" aria-hidden="true"><use href="#dp-i-{{ $role->is_active ? 'off' : 'on' }}"/></svg></button>
                                        </form>
                                    @endif
                                @endif
                                @if ($hasAccess)
                                    <a class="dp-act is-view" href="{{ route('portal.module-access.index', ['role' => $role->name]) }}" title="Module access" aria-label="Module access for {{ $display }}"><svg class="dp-ico" aria-hidden="true"><use href="#dp-i-shield"/></svg></a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="dp-empty">No {{ $selectedStatus }} user roles found.</td></tr>
                    @endforelse
                    <tr id="ur-none" hidden><td colspan="4" class="dp-empty">No roles match your search.</td></tr>
                </tbody>
            </table>
        </div>
    </section>
</div>

@if ($can['edit'])
<dialog class="dp-modal is-narrow" id="ur-modal" aria-labelledby="ur-modal-title">
    <div class="dp-mhead">
        <div><p class="dp-eyebrow">{{ $eyebrow }}</p><h2 id="ur-modal-title">Edit user role</h2><p>Update the role name and save your changes.</p></div>
        <button class="dp-x" type="button" data-dp-close aria-label="Close dialog">×</button>
    </div>
    <form id="ur-edit-form" method="POST" data-update-base="{{ url('/portal/user-roles') }}" data-ur-form novalidate>
        @csrf
        @method('PUT')
        <div class="ur-now"><span class="dp-av dp-a3" id="ur-now-initial">R</span><div><small>Editing role</small><strong id="ur-now-name">—</strong></div></div>
        <div class="ur-f">
            <label for="ur-edit-name">Role name</label>
            <input id="ur-edit-name" class="ur-input" type="text" name="name" maxlength="30" required autocomplete="off" spellcheck="false" autocapitalize="none" aria-describedby="ur-edit-err" data-ur-name>
            <small class="dp-fe" id="ur-edit-err" role="alert"></small>
            <small class="ur-help">2–30 characters: lowercase letters, numbers and underscores.</small>
        </div>
        <div class="dp-mfoot">
            <button class="dp-btn dp-ghost" type="button" data-dp-close>Cancel</button>
            <button class="dp-btn dp-solid" type="submit">Save changes</button>
        </div>
    </form>
</dialog>
@endif

<style @if ($nonce) nonce="{{ $nonce }}" @endif>
.dp [hidden] { display: none !important; }
.dp-modal.is-narrow { width: min(480px, calc(100vw - 24px)); }
.ur-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 16px; }
.ur-stat { position: relative; padding: 20px; overflow: hidden; transition: transform .2s, box-shadow .2s; }
.ur-stat:hover { transform: translateY(-3px); box-shadow: 0 12px 28px rgba(15,23,42,.07); }
.ur-stat .dp-av { width: 40px; height: 40px; margin-bottom: 14px; }
.ur-stat p { margin: 0; color: var(--mut); font-size: 13.5px; font-weight: 600; }
.ur-stat strong { display: block; margin: 3px 0 4px; font-size: 36px; line-height: 1.1; font-weight: 800; letter-spacing: -.035em; font-variant-numeric: tabular-nums; }
.ur-stat small { color: var(--mut); font-size: 12.5px; }
.ur-share { height: 8px; margin-top: 12px; }

.ur-create { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.5fr); gap: 24px; align-items: center; margin-bottom: 16px; padding: 22px; }
.ur-create h2 { margin: 0; font-size: 19px; font-weight: 800; letter-spacing: -.02em; }
.ur-create p { margin: 5px 0 0; color: var(--mut); font-size: 13.5px; }
.ur-add { display: flex; align-items: flex-start; gap: 12px; }
.ur-add .dp-btn { margin-top: 24px; }
.ur-f { display: grid; flex: 1; gap: 6px; min-width: 0; }
.ur-f label { color: #404040; font-size: 13px; font-weight: 700; }
.ur-input { width: 100%; min-height: 42px; padding: 0 13px; border: 1px solid var(--line); border-radius: 10px; color: var(--ink); background: #fff; font: 600 14px inherit; font-family: inherit; transition: border-color .15s, box-shadow .15s; }
.ur-input::placeholder { color: #a3a3a3; font-weight: 500; }
.ur-input:focus { border-color: #22c55e; box-shadow: 0 0 0 4px rgba(34,197,94,.14); outline: none; }
.ur-input[aria-invalid="true"] { border-color: #ef4444; box-shadow: 0 0 0 4px rgba(239,68,68,.12); }
.ur-help { color: var(--mut); font-size: 12px; }

.ur-ph { display: grid; grid-template-columns: 1fr; gap: 14px; }
.ur-tools { display: flex; width: 100%; flex-wrap: nowrap; align-items: center; gap: 12px; }
.ur-tools .dp-search { flex: 1 1 auto; width: auto; min-width: 0; }
.ur-tools .dp-switch { flex: none; }
.ur-role { display: grid; gap: 2px; min-width: 0; }
.ur-role strong { font-size: 14.5px; font-weight: 800; letter-spacing: -.01em; }
.ur-role code { color: var(--mut); font: 600 12px ui-monospace, SFMono-Regular, Menlo, monospace; }
.ur-sys { padding: 3px 9px; border: 1px solid var(--line); border-radius: 999px; color: #525252; background: var(--soft); font-size: 11.5px; font-weight: 700; }
.ur-users { display: flex; align-items: center; min-width: 60px; }
.ur-users b { min-width: 28px; font-weight: 800; font-variant-numeric: tabular-nums; }
.ur-inline { display: inline-block; margin: 0; vertical-align: middle; }
.dp-actions .dp-act { vertical-align: middle; }
a.dp-act { text-decoration: none; }
.ur-prot { display: inline-flex; align-items: center; gap: 6px; margin-right: 10px; color: var(--mut); font-size: 12.5px; font-weight: 700; vertical-align: middle; }
.ur-prot svg { width: 15px; height: 15px; }
.ur-now { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; padding: 12px 14px; border: 1px solid var(--line); border-radius: 12px; background: var(--soft); }
.ur-now small { display: block; color: var(--mut); font-size: 12px; }
.ur-now strong { font: 700 14px ui-monospace, SFMono-Regular, Menlo, monospace; }

@media (max-width: 900px) { .ur-stats { grid-template-columns: 1fr; } .ur-create { grid-template-columns: 1fr; gap: 14px; } }
@media (max-width: 560px) {
    .ur-add { flex-direction: column; }
    .ur-add .dp-btn { width: 100%; justify-content: center; margin-top: 0; }
    .ur-tools { flex-wrap: wrap; }
    .ur-tools .dp-search { flex-basis: 100%; }
}
</style>

<script @if ($nonce) nonce="{{ $nonce }}" @endif>
(function () {
    'use strict';
    var $ = function (s, r) { return (r || document).querySelector(s); };
    var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
    var reserved = ['admin', 'super_admin'], RE = /^[a-z][a-z0-9_]{1,29}$/;

    /* Count-up on the overview numbers */
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    $$('[data-count]').forEach(function (el) {
        var end = +el.getAttribute('data-count') || 0;
        if (reduce || !end) return;
        var t0 = null;
        el.textContent = '0';
        requestAnimationFrame(function tick(t) {
            if (t0 === null) t0 = t;
            var p = Math.min(1, (t - t0) / 900);
            el.textContent = Math.round(end * (1 - Math.pow(1 - p, 3)));
            if (p < 1) requestAnimationFrame(tick);
        });
    });

    /* Role name rules (the server must enforce the same) */
    function clean(v) { return v.toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_]/g, '').replace(/^[^a-z]+/, '').slice(0, 30); }
    function problem(v) {
        if (!v) return 'Enter a role name.';
        if (!RE.test(v)) return 'Use 2–30 characters: start with a letter, then lowercase letters, numbers or underscores.';
        if (reserved.indexOf(v) !== -1) return 'This name is reserved for a system role.';
        return '';
    }
    function show(input, msg) {
        var out = input.parentNode.querySelector('.dp-fe');
        out.textContent = msg;
        if (msg) input.setAttribute('aria-invalid', 'true'); else input.removeAttribute('aria-invalid');
    }
    $$('[data-ur-name]').forEach(function (input) {
        input.addEventListener('input', function () {
            var c = clean(input.value);
            if (c !== input.value) input.value = c;
            if (input.getAttribute('aria-invalid')) show(input, problem(c));
        });
        input.addEventListener('blur', function () { if (input.value) show(input, problem(input.value)); });
    });
    $$('form[data-ur-form]').forEach(function (form) {
        form.addEventListener('submit', function (ev) {
            var input = $('[data-ur-name]', form), msg = problem(input.value.trim());
            show(input, msg);
            if (msg) { ev.preventDefault(); input.focus(); return; }
            var btn = $('[type="submit"]', form);
            btn.disabled = true;
        });
    });

    /* Search */
    var search = $('#ur-search'), rows = $$('[data-ur-row]'), none = $('#ur-none'), counter = $('[data-ur-count]');
    if (search) search.addEventListener('input', function () {
        var q = search.value.trim().toLowerCase(), shown = 0;
        rows.forEach(function (r) {
            var hit = !q || (r.getAttribute('data-role') || '').indexOf(q) !== -1 || (r.textContent || '').toLowerCase().indexOf(q) !== -1;
            r.hidden = !hit;
            if (hit) shown++;
        });
        counter.textContent = shown;
        none.hidden = shown > 0 || !rows.length;
    });

    /* Edit dialog */
    var dlg = $('#ur-modal'), editForm = $('#ur-edit-form');
    function open(d) { if (d.showModal) d.showModal(); else d.setAttribute('open', ''); }
    function close(d) { if (d.close) d.close(); else d.removeAttribute('open'); }
    if (dlg) {
        $$('[data-ur-edit]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var name = btn.getAttribute('data-name'), input = $('#ur-edit-name');
                editForm.action = editForm.getAttribute('data-update-base') + '/' + encodeURIComponent(btn.getAttribute('data-id'));
                input.value = name; show(input, '');
                $('#ur-now-name').textContent = name;
                $('#ur-now-initial').textContent = name.charAt(0).toUpperCase();
                open(dlg);
                setTimeout(function () { input.focus(); input.select(); }, 60);
            });
        });
        dlg.addEventListener('click', function (ev) { if (ev.target === dlg || ev.target.closest('[data-dp-close]')) close(dlg); });
    }

    /* Activate / deactivate with confirmation */
    $$('form[data-ur-status]').forEach(function (form) {
        var confirmed = false;
        form.addEventListener('submit', function (ev) {
            if (confirmed) return;
            ev.preventDefault();
            var on = form.getAttribute('data-next') === '1', word = on ? 'Activate' : 'Deactivate', users = +form.getAttribute('data-users') || 0;
            var text = on ? 'The role will be available to assign again.'
                : (users ? users + ' assigned ' + (users === 1 ? 'user is' : 'users are') + ' affected. The role stays in the system but can’t be assigned.' : 'The role stays in the system but can’t be assigned.');
            var ask = window.Swal ? window.Swal.fire({
                icon: 'warning', title: word + ' “' + form.getAttribute('data-name') + '”?', text: text,
                showCancelButton: true, confirmButtonText: word, cancelButtonText: 'Cancel', confirmButtonColor: on ? '#15803d' : '#b91c1c'
            }).then(function (r) { return r.isConfirmed; }) : Promise.resolve(window.confirm(word + ' this role? ' + text));
            ask.then(function (ok) {
                if (!ok) return;
                confirmed = true;
                $('button', form).disabled = true;
                form.submit();
            });
        });
    });
})();
</script>
@endsection