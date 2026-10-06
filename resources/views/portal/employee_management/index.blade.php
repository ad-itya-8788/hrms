@extends('layouts.portal')

@section('content')
@php
    $nonce     = function_exists('csp_nonce') ? csp_nonce() : null;
    $user      = auth()->user();
    $eyebrow   = ($user->isSuperAdmin() ? 'Super admin' : ucwords(str_replace('_', ' ', $user->role))) . ' · People';
    $current   = (int) ($pagination['current_page'] ?? 1);
    $lastPage  = max(1, (int) ($pagination['last_page'] ?? 1));
    $total     = (int) ($pagination['total'] ?? 0);
    $perPage   = (int) ($pagination['per_page'] ?? 50);
    $shown     = count($employees);
    $from      = $shown ? (($current - 1) * $perPage + 1) : 0;
    $to        = ($current - 1) * $perPage + $shown;
    $activeN   = (int) $activeEmployeeCount;
    $inactiveN = (int) $inactiveEmployeeCount;
    $allN      = $activeN + $inactiveN;
    $share     = $allN > 0 ? round($activeN / $allN * 100) : 0;
@endphp

{{-- Icon sprite: one definition shared by server-rendered and JS-rendered rows --}}
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <symbol id="dp-i-view" viewBox="0 0 24 24"><path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12z"/><circle cx="12" cy="12" r="2.8"/></symbol>
    <symbol id="dp-i-edit" viewBox="0 0 24 24"><path d="M4 20h4L19.5 8.5a2.1 2.1 0 00-3-3L5 17v3z"/><path d="M14.5 7.5l3 3"/></symbol>
    <symbol id="dp-i-off" viewBox="0 0 24 24"><path d="M12 3v8"/><path d="M6.4 6.8a8 8 0 1011.2 0"/></symbol>
    <symbol id="dp-i-on" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.7 2.7L16 9.5"/></symbol>
</svg>

<style @if ($nonce) nonce="{{ $nonce }}" @endif>
/* Design tokens: navy HRMS look (matches the Exit Pass screens) */
.dp, .dp-modal {
    --navy: #0f2a5c; --navy-d: #0a1f45; --navy-l: #eef3fc; --navy-b: #d3e0f7;
    --g: var(--navy); --g-d: var(--navy-d); --g-l: var(--navy-l); --g-b: var(--navy-b);
    --ink: #0f1b3d; --mut: #64748b; --line: #e3e8ef; --soft: #f5f7fa;
}
.dp {
    max-width: 1280px; margin: 0 auto; padding: 22px 22px 18px; color: var(--ink);
    background: #fff; border: 1px solid var(--line); border-radius: 14px; box-shadow: 0 1px 3px rgba(15,23,42,.05);
    font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif; -webkit-font-smoothing: antialiased;
}
.dp *, .dp-modal *, .dp *::before { box-sizing: border-box; }
.dp svg, .dp-modal svg, .dp-ico { fill: none; stroke: currentColor; stroke-width: 1.9; stroke-linecap: round; stroke-linejoin: round; }
.dp-sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); }

.dp-in { opacity: 0; transform: translateY(8px); animation: dp-rise .45s cubic-bezier(.22,1,.36,1) forwards; animation-delay: calc(var(--i) * 60ms); }
@keyframes dp-rise { to { opacity: 1; transform: none; } }

/* Heading + buttons */
.dp-head { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 18px; }
.dp-eyebrow { margin: 0 0 4px; color: var(--mut); font-size: 12.5px; font-weight: 600; letter-spacing: .02em; }
.dp-head h1 { margin: 0; color: var(--navy-d); font-size: 28px; line-height: 1.15; font-weight: 800; letter-spacing: -.02em; }
.dp-sub { margin: 4px 0 0; color: var(--mut); font-size: 14px; }
.dp-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 40px; padding: 0 16px; border-radius: 8px;
    border: 1px solid transparent; font: 600 14px/1 inherit; font-family: inherit; cursor: pointer; text-decoration: none;
    transition: transform .15s, background .15s, box-shadow .15s, border-color .15s;
}
.dp-btn svg { width: 16px; height: 16px; }
.dp-btn:active:not(:disabled) { transform: scale(.97); }
.dp-btn:disabled { opacity: .5; cursor: not-allowed; }
.dp-solid { color: #fff; background: var(--navy); box-shadow: 0 4px 12px rgba(15,42,92,.22); }
.dp-solid:hover:not(:disabled) { background: var(--navy-d); }
.dp-ghost { color: var(--ink); background: #fff; border-color: var(--line); }
.dp-ghost:hover:not(:disabled) { background: var(--soft); border-color: #cfd7e3; }
.dp-sm { min-height: 34px; padding: 0 12px; font-size: 13px; }
.dp a:focus-visible, .dp button:focus-visible, .dp input:focus-visible, .dp select:focus-visible, .dp-modal :focus-visible { outline: 2px solid #3b6fd8; outline-offset: 2px; }

/* Inner bordered cards (like the table box on the Exit Pass page) */
.dp-card { background: #fff; border: 1px solid var(--line); border-radius: 12px; }
.dp-notice { margin-bottom: 16px; padding: 12px 16px; border-radius: 10px; font-size: 14px; font-weight: 600; animation: dp-rise .35s both; }
.dp-notice.ok { color: #166534; background: #f0fdf4; border: 1px solid #bbf7d0; }
.dp-notice.bad { color: #991b1b; background: #fef2f2; border: 1px solid #fecaca; }

/* Active share graph */
.dp-ratio { flex: 1; min-width: 220px; max-width: 420px; }
.dp-ratio-top { display: flex; justify-content: space-between; gap: 10px; font-size: 13px; color: var(--mut); }
.dp-ratio-top strong { color: var(--ink); font-size: 15px; font-weight: 800; }
.dp-rail { height: 8px; margin-top: 8px; overflow: hidden; border-radius: 999px; background: #e9eef6; }
.dp-rail i { display: block; width: var(--w); height: 100%; border-radius: inherit; background: linear-gradient(90deg, #3b6fd8, var(--navy)); transform-origin: left; transition: width .6s cubic-bezier(.22,1,.36,1); animation: dp-grow 1s cubic-bezier(.22,1,.36,1) .3s both; }
@keyframes dp-grow { from { transform: scaleX(0); } }

/* Segmented switch */
.dp-switch { display: inline-flex; padding: 4px; border-radius: 10px; background: #eef1f6; }
.dp-switch a { display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; border-radius: 8px; color: var(--mut); font-size: 13.5px; font-weight: 600; text-decoration: none; transition: background .2s, color .2s, box-shadow .2s; }
.dp-switch a:hover { color: var(--ink); }
.dp-switch a.is-on { color: var(--navy); background: #fff; box-shadow: 0 1px 4px rgba(15,23,42,.12); }
.dp-switch b { min-width: 24px; padding: 2px 8px; border-radius: 6px; color: var(--mut); background: #dfe5ee; font-size: 12px; text-align: center; font-variant-numeric: tabular-nums; }
.dp-switch .is-on b { color: #fff; background: var(--navy); }

/* Table */
.dp-scroll { overflow-x: auto; }
.dp table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
.dp th { padding: 11px 16px; border-block: 1px solid var(--line); color: #1f2a44; background: #f3f5f9; font-size: 13px; font-weight: 700; text-align: left; white-space: nowrap; }
.dp td { padding: 12px 16px; border-bottom: 1px solid #eef1f5; vertical-align: middle; }
.dp tbody tr { transition: background .15s; }
.dp tbody tr:hover { background: #f8fafd; }
.dp tbody.is-loading { opacity: .45; pointer-events: none; transition: opacity .15s; }
.dp-empty { padding: 44px 16px !important; color: var(--mut); text-align: center; }
.dp-code { padding: 3px 9px; border-radius: 6px; color: var(--navy); background: var(--navy-l); border: 1px solid var(--navy-b); font: 700 12px ui-monospace, SFMono-Regular, Menlo, monospace; }
.dp-name { display: flex; align-items: center; gap: 11px; min-width: 150px; }
.dp-name strong { font-weight: 600; }
.dp-av { display: grid; place-items: center; flex: none; width: 34px; height: 34px; border-radius: 50%; font-size: 13px; font-weight: 800; }
.dp-a0 { color: #2563eb; background: #eff6ff; } .dp-a1 { color: #7c3aed; background: #f5f3ff; } .dp-a2 { color: #ea580c; background: #fff7ed; }
.dp-a3 { color: #15803d; background: #f0fdf4; } .dp-a4 { color: #0891b2; background: #ecfeff; } .dp-a5 { color: #4f46e5; background: #eef2ff; }

/* Solid status badges (Approved / Pending / Rejected style) */
.dp-status, .ed-table .status { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 5px; color: #fff; background: #94a3b8; font-size: 12px; font-weight: 600; line-height: 1.5; }
.dp-status i, .ed-table .status i { display: none; }
.dp-status.is-active, .ed-table .status-active { color: #fff; background: #2fae5c; }
.ed-table .status-on_leave, .ed-table .status-on-leave { color: #422006; background: #fcd34d; }
.ed-table .status-notice_period, .ed-table .status-notice-period { color: #431407; background: #fdba74; }
.ed-table .status-inactive { color: #fff; background: #ef4444; }
.ed-table .employee-code { padding: 3px 9px; border-radius: 6px; color: var(--navy); background: var(--navy-l); border: 1px solid var(--navy-b); font: 700 12px ui-monospace, SFMono-Regular, Menlo, monospace; }

/* Action buttons */
.dp-actions { white-space: nowrap; text-align: right; }
.dp-act { display: inline-grid; place-items: center; width: 34px; height: 32px; padding: 0; border: 1px solid var(--line); border-radius: 7px; color: #334155; background: #fff; cursor: pointer; transition: color .15s, background .15s, border-color .15s, transform .15s; }
.dp-act + .dp-act { margin-left: 6px; }
.dp-ico { width: 16px; height: 16px; pointer-events: none; }
.dp-act:active:not(:disabled) { transform: scale(.92); }
.dp-act.is-view:hover, .dp-act.is-edit:hover, .dp-act.is-on:hover { color: var(--navy); background: var(--navy-l); border-color: var(--navy-b); }
.dp-act.is-off:hover { color: #b91c1c; background: #fef2f2; border-color: #fecaca; }
.dp-act:disabled { opacity: .45; cursor: wait; }

/* Footer + pager */
.dp-foot { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 18px; color: #475569; font-size: 13px; }
.dp-pager { display: flex; align-items: center; gap: 10px; }

/* Modals (native dialog: focus trap and Escape come built in) */
.dp-modal { width: min(640px, calc(100vw - 24px)); max-height: calc(100dvh - 24px); padding: 24px; overflow-y: auto; border: 1px solid var(--line); border-radius: 14px; color: var(--ink); background: #fff; box-shadow: 0 30px 80px rgba(15,23,42,.28); font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
.dp-modal[open] { animation: dp-pop .25s cubic-bezier(.22,1,.36,1); }
.dp-modal::backdrop { background: rgba(10,25,60,.45); backdrop-filter: blur(3px); }
@keyframes dp-pop { from { opacity: 0; transform: translateY(12px) scale(.98); } }
.dp-mhead { display: flex; justify-content: space-between; gap: 16px; margin-bottom: 18px; }
.dp-mhead h2 { margin: 0; color: var(--navy-d); font-size: 21px; font-weight: 800; letter-spacing: -.02em; }
.dp-mhead p { margin: 4px 0 0; color: var(--mut); font-size: 13.5px; }
.dp-mhead .dp-eyebrow { margin: 0 0 4px; color: var(--mut); font-size: 12.5px; }
.dp-x { width: 34px; height: 34px; flex: none; border: 1px solid var(--line); border-radius: 8px; color: #475569; background: #fff; font-size: 20px; line-height: 1; cursor: pointer; }
.dp-x:hover { color: var(--navy); background: var(--navy-l); }
.dp-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; }
.dp-grid label { display: grid; gap: 6px; color: var(--navy-d); font-size: 13px; font-weight: 700; }
.dp-grid input { width: 100%; min-height: 40px; padding: 0 13px; border: 1px solid #d5dce8; border-radius: 8px; color: var(--ink); background: #fff; font: 500 14px inherit; font-family: inherit; transition: border-color .15s, box-shadow .15s; }
.dp-grid input::placeholder { color: #a3adbd; font-weight: 500; }
.dp-grid input:focus { border-color: #3b6fd8; box-shadow: 0 0 0 3px rgba(59,111,216,.15); outline: none; }
.dp-grid input[aria-invalid="true"] { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,.12); }
.dp-fe { color: #b91c1c; font-size: 12px; font-weight: 600; line-height: 1.3; }
.dp-fe:empty { display: none; }
.dp-error { margin-top: 14px; padding: 11px 14px; border-radius: 8px; color: #991b1b; background: #fef2f2; border: 1px solid #fecaca; font-size: 13.5px; font-weight: 600; }
.dp-mfoot { display: flex; justify-content: flex-end; gap: 10px; margin-top: 22px; }
.dp-details { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin: 0; }
.dp-details div { padding: 12px 14px; border: 1px solid var(--line); border-radius: 10px; background: var(--soft); }
.dp-details dt { color: var(--mut); font-size: 12px; font-weight: 600; }
.dp-details dd { margin: 3px 0 0; font-size: 14.5px; font-weight: 700; overflow-wrap: anywhere; }

@media (max-width: 1180px) { .dp-lg { display: none; } }
@media (max-width: 860px) { .dp-md { display: none; } }
@media (max-width: 560px) {
    .dp { padding: 16px 12px; }
    .dp-grid, .dp-details { grid-template-columns: 1fr; }
    .dp-head .dp-btn { width: 100%; }
    .dp-foot { padding-inline: 12px; }
}
@media (prefers-reduced-motion: reduce) { .dp *, .dp-modal, .dp-in { animation: none !important; transition: none !important; opacity: 1; transform: none; } }
</style>

<div class="dp">

    <header class="dp-head dp-in" style="--i:0">
        <div>
            <p class="dp-eyebrow">{{ $eyebrow }}</p>
            <h1>Employee directory</h1>
            <p class="dp-sub">View and manage the people who make your organisation work.</p>
        </div>
        <div class="ed-actions">
            <button class="dp-btn dp-ghost" type="button" data-export-table="employee-table">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 4v11"/><path d="M7.5 11L12 15.5 16.5 11"/><path d="M5 19.5h14"/></svg>Export CSV
            </button>
            @if ($canCreateEmployees)
                <a class="dp-btn dp-solid" href="{{ route('portal.employees.create') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>Add employee
                </a>
            @endif
        </div>
    </header>

    {{-- Overview --}}
    <section class="dp-card ed-overview dp-in" style="--i:1" aria-label="Directory summary">
        <div class="ed-total">
            <span class="dp-av dp-a1" aria-hidden="true"><svg class="dp-ico" viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.2"/><path d="M3 19c.6-3.2 3-5 6-5s5.4 1.8 6 5"/><path d="M16 5.2a3 3 0 010 5.6"/><path d="M18 14.3c1.7.6 2.7 2.2 3 4.7"/></svg></span>
            <div><strong data-directory-total>{{ number_format($total) }}</strong><small>People in your directory</small></div>
        </div>
        <div class="dp-ratio">
            <div class="dp-ratio-top"><strong>{{ $share }}% active</strong><span>{{ number_format($activeN) }} active · {{ number_format($inactiveN) }} inactive</span></div>
            <div class="dp-rail" style="--w: {{ $share }}%"><i></i></div>
        </div>
        <span class="ed-secure"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="11" width="14" height="9" rx="2.5"/><path d="M8 11V8a4 4 0 018 0v3"/></svg>Records are stored securely</span>
    </section>

    <section class="dp-card dp-in" style="--i:2" data-directory-panel data-url="{{ $employeeDataUrl }}" data-page="{{ $current }}" data-sort="last_name" data-direction="asc">

        <div class="ed-top">
            <div class="dp-switch" role="group" aria-label="Filter employee records by active status">
                <a class="{{ $recordStatus === 'active' ? 'is-on' : '' }}" href="{{ route('portal.employees.index', ['record_status' => 'active']) }}" @if ($recordStatus === 'active') aria-current="true" @endif>Active <b data-active-count>{{ $activeN }}</b></a>
                <a class="{{ $recordStatus === 'inactive' ? 'is-on' : '' }}" href="{{ route('portal.employees.index', ['record_status' => 'inactive']) }}" @if ($recordStatus === 'inactive') aria-current="true" @endif>Inactive <b data-inactive-count>{{ $inactiveN }}</b></a>
            </div>
        </div>

        <form class="ed-filters" data-directory-filters>
            <input type="hidden" name="record_status" value="{{ $recordStatus }}">

            <label class="dp-search ed-search">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="M16 16l4 4"/></svg>
                <input type="search" name="search" value="{{ $search }}" placeholder="Search name, email or employee ID" aria-label="Search employees" autocomplete="off">
            </label>

            <div class="ed-sel">
                <select name="department_id" aria-label="Filter by department">
                    <option value="">All departments</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" {{ (string) $department->id === (string) $departmentId ? 'selected' : '' }}>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ed-sel">
                <select name="employee_type_id" aria-label="Filter by employee type">
                    <option value="">All employment types</option>
                    @foreach ($employeeTypes as $type)
                        <option value="{{ $type->id }}" {{ (string) $type->id === (string) $employeeTypeId ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ed-sel">
                <select name="employee_role_id" aria-label="Filter by employee role">
                    <option value="">All employee roles</option>
                    @foreach ($employeeRoles as $role)
                        <option value="{{ $role->id }}" {{ (string) $role->id === (string) $employeeRoleId ? 'selected' : '' }}>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ed-sel">
                <select name="status" aria-label="Filter by status">
                    <option value="">All statuses</option>
                    @foreach (['active' => 'Active', 'on_leave' => 'On leave', 'notice_period' => 'Notice period', 'inactive' => 'Inactive'] as $key => $label)
                        <option value="{{ $key }}" {{ $status === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <label class="ed-date"><span>Joined from</span><input type="date" name="from" value="{{ $joinedFrom }}"></label>
            <label class="ed-date"><span>Joined to</span><input type="date" name="to" value="{{ $joinedTo }}"></label>

            <div class="ed-apply">
                <a class="dp-btn dp-ghost" href="{{ route('portal.employees.index', ['record_status' => $recordStatus]) }}">Reset</a>
                <button class="dp-btn dp-solid filter-submit" type="submit">Apply filters</button>
            </div>
        </form>

        <div id="employee-table" class="ed-table dp-scroll" aria-live="polite">
            @include('portal.employee_management.table', ['employees' => $employees, 'actions' => true, 'canView' => true, 'canEdit' => $canEditEmployees, 'canDelete' => $canDeleteEmployees])
        </div>

        <div class="dp-foot">
            <span data-directory-summary>Showing {{ $from }}–{{ $to }} of {{ number_format($total) }} people</span>
            <div class="dp-pager">
                <button class="dp-btn dp-ghost dp-sm" type="button" data-directory-prev {{ $current <= 1 ? 'disabled' : '' }}>Previous</button>
                <span data-directory-page>Page {{ $current }} of {{ $lastPage }}</span>
                <button class="dp-btn dp-ghost dp-sm" type="button" data-directory-next {{ $current >= $lastPage ? 'disabled' : '' }}>Next</button>
            </div>
        </div>
    </section>
</div>

<style @if ($nonce) nonce="{{ $nonce }}" @endif>
.dp [hidden] { display: none !important; }
.ed-actions { display: flex; flex-wrap: wrap; gap: 10px; }

.dp-overview, .ed-overview { display: flex; flex-wrap: wrap; align-items: center; gap: 18px 32px; margin-bottom: 16px; padding: 16px 20px; }
.ed-total { display: flex; align-items: center; gap: 14px; }
.ed-total .dp-av { width: 44px; height: 44px; border-radius: 12px; }
.ed-total strong { display: block; color: var(--navy-d); font-size: 30px; line-height: 1.05; font-weight: 800; letter-spacing: -.03em; font-variant-numeric: tabular-nums; }
.ed-total small { color: var(--mut); font-size: 13px; font-weight: 600; }
.ed-overview .dp-ratio { flex: 1; min-width: 220px; max-width: none; }
.ed-secure { display: inline-flex; align-items: center; gap: 8px; padding: 7px 13px; border-radius: 8px; color: var(--navy); background: var(--navy-l); border: 1px solid var(--navy-b); font-size: 12.5px; font-weight: 600; }
.ed-secure svg { width: 15px; height: 15px; }

.ed-top { padding: 16px 18px 0; }
.ed-filters { display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 12px; align-items: end; padding: 14px 18px 18px; border-bottom: 1px solid var(--line); }
.ed-search { grid-column: span 12; width: 100%; position: relative; display: block; }
.ed-search svg { position: absolute; left: 13px; top: 50%; width: 16px; height: 16px; margin-top: -8px; color: #94a3b8; pointer-events: none; }
.ed-search input { width: 100%; min-height: 40px; padding: 0 14px 0 38px; border: 1px solid #d5dce8; border-radius: 8px; color: var(--ink); background: #fff; font: 500 14px inherit; font-family: inherit; transition: border-color .15s, box-shadow .15s; }
.ed-search input:focus { border-color: #3b6fd8; box-shadow: 0 0 0 3px rgba(59,111,216,.15); outline: none; }
.ed-sel { position: relative; grid-column: span 3; }
.ed-sel::after { content: ""; position: absolute; right: 15px; top: 50%; width: 7px; height: 7px; margin-top: -6px; border: solid #64748b; border-width: 0 2px 2px 0; transform: rotate(45deg); pointer-events: none; }
.ed-sel select, .ed-date input {
    width: 100%; min-height: 40px; padding: 0 36px 0 13px; border: 1px solid #d5dce8; border-radius: 8px; color: var(--ink); background: #fff;
    font: 500 13.5px inherit; font-family: inherit; appearance: none; -webkit-appearance: none; cursor: pointer; transition: border-color .15s, box-shadow .15s;
}
.ed-date { display: grid; grid-column: span 3; gap: 5px; color: var(--navy-d); font-size: 12.5px; font-weight: 700; }
.ed-date input { padding-right: 13px; }
.ed-sel select:focus, .ed-date input:focus { border-color: #3b6fd8; box-shadow: 0 0 0 3px rgba(59,111,216,.15); outline: none; }
.ed-apply { display: flex; justify-content: flex-end; gap: 10px; grid-column: span 6; }

.ed-table table { min-width: 760px; }
.ed-table td strong { font-weight: 600; }

@media (max-width: 1100px) { .ed-sel, .ed-date { grid-column: span 6; } .ed-apply { grid-column: span 12; } }
@media (max-width: 560px) {
    .ed-sel, .ed-date { grid-column: span 12; }
    .ed-filters, .ed-top { padding-inline: 12px; }
    .ed-actions, .ed-actions .dp-btn { width: 100%; }
    .ed-apply .dp-btn { flex: 1; }
}
</style>
@endsection