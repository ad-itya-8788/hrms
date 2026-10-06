@extends('layouts.portal')

@section('content')
@php
    $nonce      = function_exists('csp_nonce') ? csp_nonce() : null;
    $roleTitle  = ucwords(str_replace('_', ' ', $selectedRole));
    $isActive   = $isSystemRole || ($selectedRoleRecord && $selectedRoleRecord->is_active);
    $userCount  = $selectedRoleRecord ? (int) $selectedRoleRecord->users_count : 0;

    $totalPerms = 0;
    $allowedPerms = 0;
    foreach ($modules as $m) {
        foreach ($m['actions'] as $a) {
            $totalPerms++;
            if ($a['enabled']) { $allowedPerms++; }
        }
    }
    $sharePct = $totalPerms > 0 ? round($allowedPerms / $totalPerms * 100) : 0;
@endphp

{{-- Icon sprite: one definition shared by server-rendered and JS-rendered rows --}}
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <symbol id="dp-i-view" viewBox="0 0 24 24"><path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12z"/><circle cx="12" cy="12" r="2.8"/></symbol>
    <symbol id="dp-i-edit" viewBox="0 0 24 24"><path d="M4 20h4L19.5 8.5a2.1 2.1 0 00-3-3L5 17v3z"/><path d="M14.5 7.5l3 3"/></symbol>
    <symbol id="dp-i-off" viewBox="0 0 24 24"><path d="M12 3v8"/><path d="M6.4 6.8a8 8 0 1011.2 0"/></symbol>
    <symbol id="dp-i-on" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.7 2.7L16 9.5"/></symbol>
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
            <p class="dp-eyebrow">Super admin · Security</p>
            <h1>Module access</h1>
            <p class="dp-sub">Choose which modules and actions each role can access.</p>
        </div>
        <a class="dp-btn dp-ghost" href="{{ route('portal.user-roles.index') }}">Manage user roles</a>
    </header>

    @if ($errors->any())
        <div class="dp-error" role="alert" style="margin-bottom:16px">{{ $errors->first() }}</div>
    @endif

    <div class="ma-layout">

        {{-- Role list --}}
        <aside class="dp-card ma-roles dp-in" style="--i:1" aria-label="User roles">
            <h2 class="ma-side-title">User roles</h2>
            <nav id="ma-roles" class="ma-role-list">
                @foreach ($roles as $role)
                    @php
                        $system = in_array($role->name, ['admin', 'super_admin'], true);
                        $kind   = $system ? 'Unrestricted' : ($role->is_active ? 'Active' : 'Inactive');
                        $on     = $selectedRole === $role->name;
                    @endphp
                    <a class="ma-role {{ $on ? 'is-on' : '' }}" href="{{ route('portal.module-access.index', ['role' => $role->name]) }}"
                       @if ($on) aria-current="page" @endif>
                        <span class="dp-av dp-a{{ $loop->index % 6 }}">{{ mb_strtoupper(mb_substr($role->name, 0, 1)) }}</span>
                        <span class="ma-role-text">
                            <strong>{{ ucwords(str_replace('_', ' ', $role->name)) }}</strong>
                            <small><i class="ma-dot {{ $system || $role->is_active ? 'is-live' : '' }}"></i>{{ $kind }} · {{ (int) $role->users_count }} {{ \Illuminate\Support\Str::plural('user', $role->users_count) }}</small>
                        </span>
                    </a>
                @endforeach
            </nav>
        </aside>

        {{-- Permissions --}}
        <form id="ma-form" class="ma-main" method="post" action="{{ route('portal.module-access.update') }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="role" value="{{ $selectedRole }}">

            <section class="dp-card ma-summary dp-in" style="--i:2">
                <div class="ma-summary-top">
                    <div>
                        <p class="dp-eyebrow">Role permissions</p>
                        <h2>{{ $roleTitle }} modules</h2>
                        @if ($isSystemRole)
                            <p>This system role is unrestricted. Its access is always allowed and cannot be changed here.</p>
                        @elseif (! $isActive)
                            <p>This role is inactive. Activate it in User Roles before changing module access.</p>
                        @else
                            <p>Changes apply to all {{ $userCount }} {{ \Illuminate\Support\Str::plural('account', $userCount) }} assigned to this role.</p>
                        @endif
                    </div>
                    <span class="dp-status {{ $isActive ? 'is-active' : '' }}"><i></i>{{ $isSystemRole ? 'Unrestricted' : ($isActive ? 'Active role' : 'Inactive role') }}</span>
                </div>

                <div class="ma-meter">
                    <div class="ma-meter-num"><strong data-ma-allowed>{{ $allowedPerms }}</strong><span>of {{ $totalPerms }} permissions allowed</span></div>
                    <div class="dp-rail" id="ma-rail" style="--w: {{ $sharePct }}%"><i></i></div>
                </div>

                @if (! $canManageAccess)
                    <div class="ma-lock" role="note">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="11" width="14" height="9" rx="2.5"/><path d="M8 11V8a4 4 0 018 0v3"/></svg>
                        <span>Read only. These permissions can’t be edited for this role.</span>
                    </div>
                @else
                    <div class="ma-tools">
                        <label class="dp-search" for="ma-search">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="M16 16l4 4"/></svg>
                            <input id="ma-search" type="search" placeholder="Search modules" aria-label="Search modules" autocomplete="off">
                        </label>
                        <div class="ma-bulk">
                            <button class="dp-btn dp-ghost dp-sm" type="button" data-ma-bulk="1">Allow all</button>
                            <button class="dp-btn dp-ghost dp-sm" type="button" data-ma-bulk="0">Block all</button>
                        </div>
                    </div>
                @endif
            </section>

            <div class="ma-grid">
                @foreach ($modules as $module)
                    @php $count = count($module['actions']); @endphp
                    <section class="dp-card ma-module dp-in" style="--i:{{ min($loop->iteration + 2, 9) }}" data-ma-module data-label="{{ \Illuminate\Support\Str::lower($module['label']) }}">
                        <div class="ma-mhead">
                            <span class="dp-av dp-a{{ $loop->index % 6 }}">{{ mb_strtoupper(mb_substr($module['label'], 0, 1)) }}</span>
                            <div class="ma-mtitle">
                                <h3>{{ $module['label'] }}</h3>
                                <span><b data-ma-on>0</b> of {{ $count }} {{ \Illuminate\Support\Str::plural('permission', $count) }} allowed</span>
                            </div>
                            @if ($canManageAccess)
                                <label class="ma-all" title="Allow or block every permission in this module">
                                    <input type="checkbox" data-ma-all aria-label="Allow all {{ $module['label'] }} permissions">
                                    <span class="ma-all-text">All</span>
                                    <span class="ma-switch" aria-hidden="true"></span>
                                </label>
                            @endif
                        </div>

                        <div class="ma-opts">
                            @foreach ($module['actions'] as $action)
                                <label class="ma-opt {{ $action['enabled'] ? 'is-allowed' : 'is-blocked' }}">
                                    <input type="checkbox" name="access[{{ $module['key'] }}][{{ $action['key'] }}]" value="1" data-ma-perm
                                           {{ $action['enabled'] ? 'checked' : '' }} {{ $canManageAccess ? '' : 'disabled' }}>
                                    <span class="ma-copy"><strong>{{ $action['label'] }}</strong><small data-ma-state>{{ $action['enabled'] ? 'Allowed' : 'Blocked' }}</small></span>
                                    <span class="ma-switch" aria-hidden="true"></span>
                                </label>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>

            <p class="dp-empty" id="ma-none" hidden>No modules match your search.</p>

            @if ($canManageAccess)
                <div class="ma-bar" id="ma-bar" role="status" aria-live="polite">
                    <span id="ma-bar-text">Unselected permissions will be blocked when saved.</span>
                    <div class="ma-bar-actions">
                        <button class="dp-btn dp-ghost" type="button" id="ma-reset" hidden>Discard</button>
                        <button class="dp-btn dp-solid" type="submit" id="ma-save">Save access settings</button>
                    </div>
                </div>
            @endif
        </form>
    </div>
</div>

<style @if ($nonce) nonce="{{ $nonce }}" @endif>
.dp [hidden] { display: none !important; }
.ma-layout { display: grid; grid-template-columns: 280px minmax(0, 1fr); gap: 16px; align-items: start; }
.ma-roles { position: sticky; top: 16px; padding: 16px; }
.ma-side-title { margin: 2px 6px 12px; font-size: 15px; font-weight: 800; letter-spacing: -.01em; }
.ma-role-list { display: grid; gap: 4px; max-height: calc(100dvh - 140px); overflow-y: auto; }
.ma-role { position: relative; display: flex; align-items: center; gap: 11px; padding: 9px 10px; border: 1px solid transparent; border-radius: 12px; color: inherit; text-decoration: none; transition: background .15s, border-color .15s; }
.ma-role:hover { background: var(--soft); }
.ma-role.is-on { background: var(--g-l); border-color: var(--g-b); }
.ma-role.is-on::before { content: ""; position: absolute; left: -17px; top: 12px; bottom: 12px; width: 4px; border-radius: 0 4px 4px 0; background: var(--g); }
.ma-role-text { min-width: 0; }
.ma-role-text strong, .ma-role-text small { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ma-role-text strong { font-size: 13.5px; font-weight: 700; }
.ma-role.is-on strong { color: var(--g-d); }
.ma-role-text small { display: flex; align-items: center; gap: 6px; margin-top: 2px; color: var(--mut); font-size: 12px; }
.ma-dot { width: 7px; height: 7px; flex: none; border-radius: 50%; background: #d4d4d4; }
.ma-dot.is-live { background: #22c55e; }

.ma-main { display: grid; gap: 16px; min-width: 0; }
.ma-summary { padding: 22px; }
.ma-summary-top { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 14px; }
.ma-summary h2 { margin: 0; font-size: 22px; font-weight: 800; letter-spacing: -.02em; }
.ma-summary p:not(.dp-eyebrow) { margin: 6px 0 0; max-width: 560px; color: var(--mut); font-size: 13.5px; line-height: 1.5; }
.ma-summary .dp-eyebrow { margin-bottom: 4px; }
.ma-meter { margin-top: 20px; }
.ma-meter-num { display: flex; align-items: baseline; gap: 10px; }
.ma-meter-num strong { font-size: 34px; font-weight: 800; letter-spacing: -.03em; line-height: 1; font-variant-numeric: tabular-nums; }
.ma-meter-num span { color: var(--mut); font-size: 13.5px; font-weight: 600; }
.ma-tools { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-top: 18px; padding-top: 18px; border-top: 1px solid var(--line); }
.ma-bulk { display: flex; gap: 8px; }
.ma-lock { display: flex; align-items: center; gap: 10px; margin-top: 18px; padding: 12px 14px; border: 1px solid #e5e5e5; border-radius: 11px; color: #525252; background: var(--soft); font-size: 13.5px; font-weight: 600; }
.ma-lock svg { width: 18px; height: 18px; flex: none; }

.ma-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px; }
.ma-module { padding: 18px; transition: box-shadow .2s, transform .2s; }
.ma-module:hover { box-shadow: 0 12px 28px rgba(15,23,42,.06); }
.ma-mhead { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
.ma-mtitle { flex: 1; min-width: 0; }
.ma-mtitle h3 { margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 15.5px; font-weight: 800; letter-spacing: -.01em; }
.ma-mtitle span { color: var(--mut); font-size: 12.5px; }
.ma-mtitle b { color: var(--ink); font-weight: 800; }
.ma-opts { display: grid; gap: 8px; }
.ma-opt { position: relative; display: flex; align-items: center; gap: 12px; padding: 11px 13px; border: 1px solid var(--line); border-radius: 11px; background: #fff; cursor: pointer; transition: background .15s, border-color .15s; }
.ma-opt:hover { border-color: #d4d4d4; }
.ma-opt.is-allowed { background: var(--g-l); border-color: var(--g-b); }
.ma-opt input:disabled ~ .ma-copy { opacity: .7; }
.ma-copy { flex: 1; min-width: 0; }
.ma-copy strong, .ma-copy small { display: block; }
.ma-copy strong { font-size: 13.5px; font-weight: 700; }
.ma-copy small { margin-top: 1px; color: var(--mut); font-size: 12px; font-weight: 600; }
.is-allowed .ma-copy small { color: var(--g-d); }

/* Switch: the real checkbox stays focusable and screen-reader visible */
.ma-opt input, .ma-all input { position: absolute; width: 1px; height: 1px; opacity: 0; }
.ma-switch { position: relative; display: block; flex: none; width: 40px; height: 24px; border-radius: 999px; background: #d4d4d4; transition: background .2s; }
.ma-switch::after { content: ""; position: absolute; top: 3px; left: 3px; width: 18px; height: 18px; border-radius: 50%; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.25); transition: transform .22s cubic-bezier(.22,1,.36,1); }
input:checked ~ .ma-switch { background: var(--g); }
input:checked ~ .ma-switch::after { transform: translateX(16px); }
input:indeterminate ~ .ma-switch { background: #86efac; }
input:indeterminate ~ .ma-switch::after { transform: translateX(8px); }
input:focus-visible ~ .ma-switch { outline: 2px solid #22c55e; outline-offset: 2px; }
input:disabled ~ .ma-switch { opacity: .55; }
.ma-all { position: relative; display: flex; align-items: center; flex: none; gap: 8px; cursor: pointer; }
.ma-all-text { color: var(--mut); font-size: 12.5px; font-weight: 700; }
.ma-all input:checked ~ .ma-all-text { color: var(--g-d); }

/* Save bar */
.ma-bar { position: sticky; bottom: 16px; z-index: 5; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 18px; border: 1px solid var(--line); border-radius: 14px; background: rgba(255,255,255,.94); backdrop-filter: blur(8px); box-shadow: 0 10px 30px rgba(15,23,42,.1); color: var(--mut); font-size: 13.5px; font-weight: 600; transition: border-color .2s, box-shadow .2s; }
.ma-bar.is-dirty { border-color: var(--g-b); box-shadow: 0 12px 34px rgba(21,128,61,.18); color: var(--g-d); }
.ma-bar-actions { display: flex; gap: 10px; }

@media (max-width: 980px) {
    .ma-layout { grid-template-columns: 1fr; }
    .ma-roles { position: static; }
    .ma-role-list { display: flex; max-height: none; overflow-x: auto; padding-bottom: 4px; }
    .ma-role { flex: none; min-width: 190px; }
    .ma-role.is-on::before { display: none; }
}
@media (max-width: 560px) {
    .ma-grid { grid-template-columns: 1fr; }
    .ma-bar-actions, .ma-bar-actions .dp-btn { width: 100%; justify-content: center; }
}
</style>

<script @if ($nonce) nonce="{{ $nonce }}" @endif>
(function () {
    'use strict';
    var form = document.getElementById('ma-form');
    if (!form) return;
    var perms = Array.prototype.slice.call(form.querySelectorAll('[data-ma-perm]'));
    var modules = Array.prototype.slice.call(form.querySelectorAll('[data-ma-module]'));
    var bar = document.getElementById('ma-bar'), save = document.getElementById('ma-save'), reset = document.getElementById('ma-reset'), text = document.getElementById('ma-bar-text');
    var initial = perms.map(function (p) { return p.checked; });
    var idleText = text ? text.textContent : '', dirty = false, leaving = false;

    function sync() {
        var on = 0, changes = 0;
        perms.forEach(function (p, i) {
            var box = p.closest('.ma-opt');
            box.classList.toggle('is-allowed', p.checked);
            box.classList.toggle('is-blocked', !p.checked);
            box.querySelector('[data-ma-state]').textContent = p.checked ? 'Allowed' : 'Blocked';
            if (p.checked) on++;
            if (p.checked !== initial[i]) changes++;
        });
        modules.forEach(function (m) {
            var list = m.querySelectorAll('[data-ma-perm]'), n = 0;
            Array.prototype.forEach.call(list, function (p) { if (p.checked) n++; });
            m.querySelector('[data-ma-on]').textContent = n;
            var all = m.querySelector('[data-ma-all]');
            if (all) { all.checked = n === list.length && n > 0; all.indeterminate = n > 0 && n < list.length; }
        });
        document.querySelector('[data-ma-allowed]').textContent = on;
        document.getElementById('ma-rail').style.setProperty('--w', (perms.length ? Math.round(on / perms.length * 100) : 0) + '%');
        dirty = changes > 0;
        if (bar) {
            bar.classList.toggle('is-dirty', dirty);
            text.textContent = dirty ? changes + ' unsaved ' + (changes === 1 ? 'change' : 'changes') + '. Unselected permissions will be blocked.' : idleText;
            reset.hidden = !dirty;
            save.disabled = !dirty;
        }
    }

    form.addEventListener('change', function (ev) {
        var all = ev.target.closest('[data-ma-all]');
        if (all) {
            Array.prototype.forEach.call(all.closest('[data-ma-module]').querySelectorAll('[data-ma-perm]:not(:disabled)'), function (p) { p.checked = all.checked; });
        }
        sync();
    });

    Array.prototype.forEach.call(form.querySelectorAll('[data-ma-bulk]'), function (b) {
        b.addEventListener('click', function () {
            var value = b.getAttribute('data-ma-bulk') === '1';
            perms.forEach(function (p) { if (!p.disabled) p.checked = value; });
            sync();
        });
    });

    if (reset) reset.addEventListener('click', function () {
        perms.forEach(function (p, i) { p.checked = initial[i]; });
        sync();
    });

    var search = document.getElementById('ma-search'), none = document.getElementById('ma-none');
    if (search) search.addEventListener('input', function () {
        var q = search.value.trim().toLowerCase(), shown = 0;
        modules.forEach(function (m) {
            var hit = !q || m.getAttribute('data-label').indexOf(q) !== -1;
            m.hidden = !hit;
            if (hit) shown++;
        });
        none.hidden = shown > 0;
    });

    /* Don't lose unsaved work by switching role or closing the tab */
    document.getElementById('ma-roles').addEventListener('click', function (ev) {
        var link = ev.target.closest('a');
        if (!link || !dirty) return;
        if (window.confirm('Discard your unsaved changes?')) leaving = true; else ev.preventDefault();
    });
    window.addEventListener('beforeunload', function (ev) {
        if (dirty && !leaving) { ev.preventDefault(); ev.returnValue = ''; }
    });
    form.addEventListener('submit', function () {
        leaving = true;
        if (save) { save.disabled = true; save.textContent = 'Saving…'; }
    });

    sync();
})();
</script>
@endsection