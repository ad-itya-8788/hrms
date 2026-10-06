@extends('layouts.portal')

@section('content')
@php
    $user = auth()->user();
    $isSuper = $user->isSuperAdmin();
    $canViewEmployees = $user->hasPermission('employees', 'view');
    $nonce = function_exists('csp_nonce') ? csp_nonce() : null;

    $can = [];
    foreach (['create', 'edit', 'delete'] as $action) {
        $can[$action] = $user->hasPermission('employee_roles', $action);
    }

    $activeN = (int) $activeRoleCount;
    $inactiveN = (int) $inactiveRoleCount;
    $allN = $activeN + $inactiveN;
    $share = $allN > 0 ? round($activeN / $allN * 100) : 0;

    $eyebrow = ($isSuper ? 'Super admin' : ucwords(str_replace('_', ' ', $user->role))) . ' · Organisation';
    $maxEmp = max(1, (int) $employeeRoles->max('employees_count'));
@endphp

{{-- Shared UI for portal list pages (departments, employee types): icon sprite + base styles. --}}
@php $nonce = function_exists('csp_nonce') ? csp_nonce() : null; @endphp
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
            <p class="dp-eyebrow">{{ $eyebrow }}</p>
            <h1>Employee roles</h1>
            <p class="dp-sub">Manage job roles available for employee records.</p>
        </div>

        @if ($can['create'])
            <button class="dp-btn dp-solid" type="button" data-role-create>
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Add employee role
            </button>
        @endif
    </header>

    <div class="dp-notice" id="er-alert" role="status" aria-live="polite" hidden></div>

    <nav class="dp-switch er-tabs dp-in" style="--i:1" aria-label="Organisation lists">
        <a href="{{ route('portal.departments.index') }}">Departments</a>
        <a href="{{ route('portal.employee-types.index') }}">Employee types</a>
        <a class="is-on" href="{{ route('portal.employee-roles.index') }}" aria-current="page">
            Employee roles
        </a>
    </nav>

    <section class="dp-card dp-overview dp-in" style="--i:2" aria-label="Role status overview">
        <div class="dp-switch" role="group" aria-label="Filter employee roles by status">
            <a class="{{ $selectedStatus === 'active' ? 'is-on' : '' }}"
               href="{{ route('portal.employee-roles.index', ['status' => 'active']) }}"
               @if ($selectedStatus === 'active') aria-current="true" @endif>
                Active <b data-active-count>{{ $activeN }}</b>
            </a>

            <a class="{{ $selectedStatus === 'inactive' ? 'is-on' : '' }}"
               href="{{ route('portal.employee-roles.index', ['status' => 'inactive']) }}"
               @if ($selectedStatus === 'inactive') aria-current="true" @endif>
                Inactive <b data-inactive-count>{{ $inactiveN }}</b>
            </a>
        </div>

        <div class="dp-ratio">
            <div class="dp-ratio-top">
                <strong><span data-share>{{ $share }}</span>% active</strong>
                <span>of all employee roles</span>
            </div>
            <div class="dp-rail" id="er-ratio" style="--w: {{ $share }}%">
                <i></i>
            </div>
        </div>
    </section>

    <section class="dp-card dp-in" style="--i:3"
        id="employee-role-panel"
        data-status="{{ e($selectedStatus) }}"
        data-can-edit="{{ $can['edit'] ? '1' : '0' }}"
        data-can-toggle="{{ $can['delete'] ? '1' : '0' }}"
        data-can-view-employees="{{ $canViewEmployees ? '1' : '0' }}"
        data-store-url="{{ route('portal.data.employee-roles.store') }}"
        data-update-url="{{ route('portal.data.employee-roles.update', '__role__') }}"
        data-status-url="{{ route('portal.data.employee-roles.status', '__role__') }}">

        <div class="dp-ph">
            <div>
                <h2>Employee role list</h2>
                <p>
                    <span data-visible-count>{{ $employeeRoles->count() }}</span>
                    {{ $selectedStatus }} employee roles
                </p>
            </div>

            <label class="dp-search" aria-label="Search employee roles">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="11" cy="11" r="6.5"/>
                    <path d="m16 16 4.5 4.5"/>
                </svg>
                <input type="search" id="employee-role-search"
                    placeholder="Search roles or descriptions..."
                    autocomplete="off">
            </label>
        </div>

        <div class="dp-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Role</th>
                        <th class="dp-md">Description</th>
                        @if ($canViewEmployees)
                            <th>Employees</th>
                        @endif
                        <th>Status</th>
                        <th><span class="dp-sr">Actions</span></th>
                    </tr>
                </thead>

                <tbody id="employee-role-rows">
                    @forelse ($employeeRoles as $role)
                        <tr
                            data-role-row="{{ $role->id }}"
                            data-active="{{ $role->is_active ? '1' : '0' }}"
                            data-name="{{ e($role->name) }}"
                            data-description="{{ e($role->description) }}">

                            <td>
                                <div class="dp-name">
                                    <span class="dp-av dp-a{{ $role->id % 6 }}">
                                        {{ mb_strtoupper(mb_substr($role->name, 0, 1)) }}
                                    </span>
                                    <strong data-role-name>{{ $role->name }}</strong>
                                </div>
                            </td>

                            <td class="dp-md er-desc" data-role-description>
                                {{ $role->description ?: '—' }}
                            </td>

                            @if ($canViewEmployees)
                                <td>
                                    <div class="er-emp">
                                        <b data-role-employees>{{ (int) $role->employees_count }}</b>
                                        <span class="dp-rail er-bar" aria-hidden="true">
                                            <i style="--w: {{ round($role->employees_count / $maxEmp * 100) }}%"></i>
                                        </span>
                                    </div>
                                </td>
                            @endif

                            <td>
                                <span class="dp-status {{ $role->is_active ? 'is-active' : '' }}">
                                    <i></i>
                                    {{ $role->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td class="dp-actions">
                                @if ($can['edit'])
                                    <button class="dp-act is-edit"
                                        type="button"
                                        title="Edit role"
                                        aria-label="Edit {{ $role->name }}"
                                        data-role-edit="{{ $role->id }}">
                                        <svg class="dp-ico" aria-hidden="true">
                                            <use href="#dp-i-edit"/>
                                        </svg>
                                    </button>
                                @endif

                                @if ($can['delete'])
                                    @php $label = $role->is_active ? 'Deactivate' : 'Activate'; @endphp
                                    <button
                                        class="dp-act {{ $role->is_active ? 'is-off' : 'is-on' }}"
                                        type="button"
                                        title="{{ $label }} role"
                                        aria-label="{{ $label }} {{ $role->name }}"
                                        data-role-status="{{ $role->id }}">
                                        <svg class="dp-ico" aria-hidden="true">
                                            <use href="#dp-i-{{ $role->is_active ? 'off' : 'on' }}"/>
                                        </svg>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr data-role-empty>
                            <td colspan="5" class="dp-empty">
                                No {{ $selectedStatus }} employee roles found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="dp-foot">
            <span>Showing <strong data-visible-count>{{ $employeeRoles->count() }}</strong> roles</span>
            <span class="er-live" id="er-live" aria-live="polite"></span>
        </div>
    </section>
</div>

@if ($can['create'] || $can['edit'])
<dialog class="dp-modal er-modal" id="employee-role-modal" aria-labelledby="employee-role-modal-title">
    <div class="dp-mhead">
        <div>
            <p class="dp-eyebrow">{{ $eyebrow }}</p>
            <h2 id="employee-role-modal-title">Add employee role</h2>
            <p>Create a clear job role that can be assigned to employees.</p>
        </div>
        <button class="dp-x" type="button" data-dp-close aria-label="Close dialog">×</button>
    </div>

    <form id="employee-role-form" novalidate>
        @csrf

        <div class="dp-grid is-one">
            <label>
                Role name
                <input
                    name="name"
                    maxlength="120"
                    required
                    autocomplete="off"
                    placeholder="e.g. Software Developer"
                    aria-describedby="er-e-name">
                <small class="dp-fe" id="er-e-name" role="alert"></small>
            </label>

            <label>
                Description <span class="er-opt">Optional</span>
                <input
                    name="description"
                    maxlength="255"
                    autocomplete="off"
                    placeholder="e.g. Develops and maintains software applications"
                    aria-describedby="er-e-description">
                <small class="dp-fe" id="er-e-description" role="alert"></small>
            </label>
        </div>

        <div class="dp-error" role="alert" hidden></div>

        <div class="dp-mfoot">
            <button class="dp-btn dp-ghost" type="button" data-dp-close>Cancel</button>
            <button class="dp-btn dp-solid" type="submit">Save role</button>
        </div>
    </form>
</dialog>
@endif

<style @if ($nonce) nonce="{{ $nonce }}" @endif>
    .er-tabs {
        margin-bottom: 16px;
        overflow-x: auto;
        scrollbar-width: none;
    }

    .er-tabs::-webkit-scrollbar {
        display: none;
    }

    .er-tabs a {
        white-space: nowrap;
    }

    .er-desc {
        max-width: 380px;
        color: #525252;
        overflow-wrap: anywhere;
    }

    .er-emp {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 120px;
    }

    .er-emp b {
        min-width: 28px;
        font-weight: 800;
        font-variant-numeric: tabular-nums;
    }

    .er-bar {
        flex: 1;
        height: 8px;
        margin: 0;
        max-width: 140px;
    }

    .er-bar i {
        animation-delay: .5s;
    }

    .er-opt {
        margin-left: 6px;
        color: var(--mut);
        font-size: 12px;
        font-weight: 500;
    }

    .er-modal {
        width: min(500px, calc(100vw - 24px));
    }

    .er-live {
        min-height: 18px;
        color: var(--mut);
        font-size: 12px;
    }

    #employee-role-search {
        width: min(340px, 100%);
    }

    .dp tbody tr {
        transition: background .15s, opacity .3s, transform .3s;
    }

    .dp tbody tr.is-leaving {
        opacity: 0;
        transform: translateX(14px);
    }

    .dp tbody tr.is-new {
        animation: dp-rise .5s cubic-bezier(.22,1,.36,1) both;
        background: var(--g-l);
    }

    @media (max-width: 650px) {
        .er-emp {
            min-width: 90px;
        }

        .er-bar {
            display: none;
        }

        #employee-role-search {
            width: 100%;
        }
    }
</style>

<script @if ($nonce) nonce="{{ $nonce }}" @endif>
(function () {
    'use strict';

    var $ = function (s, r) {
        return (r || document).querySelector(s);
    };

    var panel = $('#employee-role-panel');
    var rows = $('#employee-role-rows');
    var form = $('#employee-role-form');
    var dlg = $('#employee-role-modal');
    var box = $('#er-alert');
    var create = $('[data-role-create]');
    var search = $('#employee-role-search');
    var live = $('#er-live');

    if (!panel || !rows) return;

    var canEdit = panel.dataset.canEdit === '1';
    var canToggle = panel.dataset.canToggle === '1';
    var canViewEmployees = panel.dataset.canViewEmployees === '1';
    var status = panel.dataset.status;

    var noticeTimer;
    var meta = $('meta[name="csrf-token"]');
    var tok = form && form.querySelector('[name="_token]');
    var csrf = meta ? meta.content : (tok ? tok.value : '');

    function h(tag, cls, text) {
        var e = document.createElement(tag);
        if (cls) e.className = cls;
        if (text != null) e.textContent = text;
        return e;
    }

    function request(url, o) {
        o = o || {};

        return fetch(url, {
            method: o.method || 'GET',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: o.body ? JSON.stringify(o.body) : undefined
        }).then(function (r) {
            return r.json().catch(function () {
                return {};
            }).then(function (p) {
                if (!r.ok) {
                    var k = p.errors ? Object.keys(p.errors) : [];
                    var e = new Error(
                        k.length
                            ? p.errors[k[0]][0]
                            : p.message || 'The request failed.'
                    );
                    e.fields = p.errors || null;
                    throw e;
                }

                return p;
            });
        });
    }

    function notify(msg, bad) {
        if (!box) return;

        box.textContent = msg;
        box.className = 'dp-notice ' + (bad ? 'bad' : 'ok');
        box.hidden = false;

        clearTimeout(noticeTimer);

        noticeTimer = setTimeout(function () {
            box.hidden = true;
        }, 7000);
    }

    function open(d) {
        if (!d) return;
        if (d.showModal) d.showModal();
        else d.setAttribute('open', '');
    }

    function close(d) {
        if (!d) return;
        if (d.close) d.close();
        else d.removeAttribute('open');
    }

    function tpl(t, id) {
        return t.replace('__role__', encodeURIComponent(id));
    }

    function icon(name) {
        var NS = 'http://www.w3.org/2000/svg';
        var XL = 'http://www.w3.org/1999/xlink';

        var svg = document.createElementNS(NS, 'svg');
        var use = document.createElementNS(NS, 'use');

        svg.setAttribute('class', 'dp-ico');
        svg.setAttribute('aria-hidden', 'true');

        use.setAttribute('href', '#dp-i-' + name);
        use.setAttributeNS(XL, 'xlink:href', '#dp-i-' + name);

        svg.appendChild(use);

        return svg;
    }

    function iconButton(kind, label, who, cls, attr, value) {
        var b = h('button', 'dp-act ' + cls);

        b.type = 'button';
        b.title = label + ' role';
        b.setAttribute('aria-label', label + ' ' + who);
        b.setAttribute(attr, value);
        b.appendChild(icon(kind));

        return b;
    }

    function td(tr, cls) {
        var c = h('td', cls);
        tr.appendChild(c);
        return c;
    }

    function buildRow(t) {
        var tr = h('tr');

        tr.setAttribute('data-role-row', t.id);
        tr.setAttribute('data-active', t.is_active ? '1' : '0');
        tr.setAttribute('data-name', t.name || '');
        tr.setAttribute('data-description', t.description || '');

        var name = h('div', 'dp-name');

        name.appendChild(
            h(
                'span',
                'dp-av dp-a' + (Number(t.id) % 6),
                String(t.name || '?').charAt(0).toUpperCase()
            )
        );

        name.appendChild(h('strong', null, t.name));

        td(tr).appendChild(name);

        td(tr, 'dp-md er-desc').textContent = t.description || '—';

        if (canViewEmployees) {
            var emp = h('div', 'er-emp');
            var bar = h('span', 'dp-rail er-bar');

            emp.appendChild(
                h('b', null, String(Number(t.employees_count) || 0))
            );

            bar.setAttribute('aria-hidden', 'true');

            var barI = h('i');
            barI.style.setProperty('--w', '0%');
            bar.appendChild(barI);

            emp.appendChild(bar);
            td(tr).appendChild(emp);
        }

        var st = h(
            'span',
            'dp-status' + (t.is_active ? ' is-active' : '')
        );

        st.appendChild(h('i'));
        st.appendChild(
            document.createTextNode(t.is_active ? 'Active' : 'Inactive')
        );

        td(tr).appendChild(st);

        var act = td(tr, 'dp-actions');

        if (canEdit) {
            act.appendChild(
                iconButton(
                    'edit',
                    'Edit',
                    t.name,
                    'is-edit',
                    'data-role-edit',
                    t.id
                )
            );
        }

        if (canToggle) {
            act.appendChild(
                iconButton(
                    t.is_active ? 'off' : 'on',
                    t.is_active ? 'Deactivate' : 'Activate',
                    t.name,
                    t.is_active ? 'is-off' : 'is-on',
                    'data-role-status',
                    t.id
                )
            );
        }

        return tr;
    }

    function refresh() {
        var list = rows.querySelectorAll('[data-role-row]');
        var max = 1;
        var empty = $('[data-role-empty]', rows);

        if (canViewEmployees) {
            Array.prototype.forEach.call(list, function (r) {
                max = Math.max(
                    max,
                    +r.querySelector('.er-emp b').textContent || 0
                );
            });

            Array.prototype.forEach.call(list, function (r) {
                var bar = r.querySelector('.er-bar i');

                if (bar) {
                bar.style.setProperty(
                    '--w',
                    Math.round(
                        (+r.querySelector('.er-emp b').textContent || 0) /
                        max * 100
                    ) + '%'
                );
                }
            });
        }

        $('[data-visible-count]').textContent = list.length;

        if (!list.length && !empty) {
            var tr = h('tr');
            var c = h(
                'td',
                'dp-empty',
                'No ' + status + ' employee roles found.'
            );

            tr.setAttribute('data-role-empty', '');
            c.colSpan = canViewEmployees ? 5 : 4;
            tr.appendChild(c);
            rows.appendChild(tr);
        } else if (list.length && empty) {
            empty.remove();
        }
    }

    function counters(from, to) {
        var a = $('[data-active-count]');
        var i = $('[data-inactive-count]');

        if (!a || !i) return;

        if (from) {
            var f = from === 'active' ? a : i;
            f.textContent = Math.max(
                0,
                (+f.textContent || 0) - 1
            );
        }

        if (to) {
            var t = to === 'active' ? a : i;
            t.textContent = (+t.textContent || 0) + 1;
        }

        var x = +a.textContent || 0;
        var all = x + (+i.textContent || 0);
        var s = all ? Math.round(x / all * 100) : 0;

        $('#er-ratio').style.setProperty('--w', s + '%');
        $('[data-share]').textContent = s;
    }

    var rules = {
        name: {
            re: /^.{2,120}$/,
            msg: 'Enter a role name (2–120 characters).',
            required: true
        },
        description: {
            re: /^.{0,255}$/,
            msg: 'Keep the description under 255 characters.',
            required: false
        }
    };

    function setError(name, msg) {
        var input = form.elements[name];
        var out = $('#er-e-' + name, form);

        if (!input || !out) return;

        out.textContent = msg || '';

        if (msg) {
            input.setAttribute('aria-invalid', 'true');
        } else {
            input.removeAttribute('aria-invalid');
        }
    }

    function clearErrors() {
        Object.keys(rules).forEach(function (n) {
            setError(n, '');
        });

        form.querySelector('.dp-error').hidden = true;
    }

    function check(name) {
        var v = form.elements[name].value.trim();
        var msg = '';

        if (!v && rules[name].required) {
            msg = 'This field is required.';
        } else if (v && !rules[name].re.test(v)) {
            msg = rules[name].msg;
        }

        setError(name, msg);

        return !msg;
    }

    if (form) {
        Object.keys(rules).forEach(function (name) {
            var input = form.elements[name];

            input.addEventListener('input', function () {
                if (input.getAttribute('aria-invalid')) {
                    check(name);
                }
            });

            input.addEventListener('blur', function () {
                if (input.value) check(name);
            });
        });
    }

    if (create && form) {
        create.addEventListener('click', function () {
            form.reset();
            delete form.dataset.roleId;
            clearErrors();

            $('#employee-role-modal-title').textContent =
                'Add employee role';

            form.querySelector('[type="submit"]').textContent =
                'Save role';

            open(dlg);

            setTimeout(function () {
                form.elements.name.focus();
            }, 50);
        });
    }

    if (form) {
        form.addEventListener('submit', function (ev) {
            ev.preventDefault();

            var bad = Object.keys(rules).filter(function (n) {
                return !check(n);
            });

            if (bad.length) {
                form.elements[bad[0]].focus();
                return;
            }

            var id = form.dataset.roleId;
            var btn = form.querySelector('[type="submit"]');
            var label = btn.textContent;

            var body = {
                name: form.elements.name.value.trim(),
                description: form.elements.description.value.trim()
            };

            btn.disabled = true;
            btn.textContent = 'Saving…';

            request(
                id
                    ? tpl(panel.dataset.updateUrl, id)
                    : panel.dataset.storeUrl,
                {
                    method: id ? 'PUT' : 'POST',
                    body: body
                }
            )
            .then(function (p) {
                close(dlg);

                notify(
                    p.message ||
                    'Role saved successfully. Loading latest data…',
                    false
                );

                /*
                 * Always reload from the server after a successful save.
                 * This guarantees the newly created/updated role is rendered
                 * from the authoritative database response.
                 */
                setTimeout(function () {
                    window.location.reload();
                }, 250);
            })
            .catch(function (err) {
                var mapped = [];
                var b = form.querySelector('.dp-error');

                Object.keys(err.fields || {}).forEach(function (k) {
                    if (rules[k]) {
                        setError(
                            k,
                            [].concat(err.fields[k])[0]
                        );
                        mapped.push(k);
                    }
                });

                if (mapped.length) {
                    b.hidden = true;
                    form.elements[mapped[0]].focus();
                    return;
                }

                b.textContent =
                    err.message || 'Unable to save employee role.';

                b.hidden = false;
            })
            .then(function () {
                btn.disabled = false;
                btn.textContent = label;
            });
        });
    }

    rows.addEventListener('click', function (ev) {
        var eb = ev.target.closest('[data-role-edit]');
        var sb = ev.target.closest('[data-role-status]');

        if (eb && form) {
            var row = eb.closest('tr');

            form.reset();
            clearErrors();

            form.dataset.roleId =
                row.getAttribute('data-role-row');

            form.elements.name.value =
                row.getAttribute('data-name') || '';

            form.elements.description.value =
                row.getAttribute('data-description') || '';

            $('#employee-role-modal-title').textContent =
                'Edit employee role';

            form.querySelector('[type="submit"]').textContent =
                'Save changes';

            open(dlg);

            setTimeout(function () {
                form.elements.name.focus();
            }, 50);
        }

        if (sb) {
            var tr = sb.closest('tr');
            var id = sb.getAttribute('data-role-status');
            var on = tr.getAttribute('data-active') !== '1';
            var word = on ? 'Activate' : 'Deactivate';

            var ask = window.Swal
                ? window.Swal.fire({
                    icon: 'warning',
                    title: word + ' employee role?',
                    text: on
                        ? 'The role will be available for employee records.'
                        : 'The role will stay in the system but become unavailable for new employee records.',
                    showCancelButton: true,
                    confirmButtonText: word,
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: on
                        ? '#15803d'
                        : '#b91c1c'
                }).then(function (r) {
                    return r.isConfirmed;
                })
                : Promise.resolve(
                    window.confirm(
                        word + ' this employee role?'
                    )
                );

            ask.then(function (ok) {
                if (!ok) return;

                sb.disabled = true;

                request(
                    tpl(panel.dataset.statusUrl, id),
                    {
                        method: 'PATCH',
                        body: {
                            is_active: on ? 1 : 0
                        }
                    }
                )
                .then(function (p) {
                    notify(
                        p.message || 'Role status updated.',
                        false
                    );

                    counters(
                        on ? 'inactive' : 'active',
                        on ? 'active' : 'inactive'
                    );

                    tr.classList.add('is-leaving');

                    setTimeout(function () {
                        tr.remove();
                        refresh();
                    }, 300);
                })
                .catch(function (err) {
                    notify(
                        err.message ||
                        'Unable to update role status.',
                        true
                    );

                    sb.disabled = false;
                });
            });
        }
    });

    if (dlg) {
        dlg.addEventListener('click', function (ev) {
            if (
                ev.target === dlg ||
                ev.target.closest('[data-dp-close]')
            ) {
                close(dlg);
            }
        });

        dlg.addEventListener('close', function () {
            if (form) {
                form.reset();
                clearErrors();
                delete form.dataset.roleId;
            }
        });
    }

    /*
     * Client-side search.
     * It filters the currently loaded server-side status list without
     * changing the database or URL.
     */
    if (search) {
        search.addEventListener('input', function () {
            var q = search.value.trim().toLowerCase();
            var list = rows.querySelectorAll('[data-role-row]');
            var visible = 0;

            Array.prototype.forEach.call(list, function (row) {
                var name =
                    (row.getAttribute('data-name') || '').toLowerCase();

                var description =
                    (row.getAttribute('data-description') || '').toLowerCase();

                var match =
                    !q ||
                    name.indexOf(q) !== -1 ||
                    description.indexOf(q) !== -1;

                row.hidden = !match;

                if (match) visible++;
            });

            if (live) {
                live.textContent = q
                    ? visible + ' matching role' +
                        (visible === 1 ? '' : 's')
                    : '';
            }
        });
    }

    refresh();
})();
</script>
@endsection
