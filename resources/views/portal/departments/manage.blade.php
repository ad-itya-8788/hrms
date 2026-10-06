@extends('layouts.portal')

@section('content')
    @php
        $user = auth()->user();
        $isSuper = $user->isSuperAdmin();
        $nonce = function_exists('csp_nonce') ? csp_nonce() : null;
        $can = [];
        foreach (['create', 'view', 'edit', 'delete'] as $action) {
            $can[$action] = $user->hasPermission('departments', $action);
        }
        $activeN = (int) $activeDepartmentCount;
        $inactiveN = (int) $inactiveDepartmentCount;
        $allN = $activeN + $inactiveN;
        $share = $allN > 0 ? round(($activeN / $allN) * 100) : 0;
        $eyebrow = ($isSuper ? 'Super admin' : ucwords(str_replace('_', ' ', $user->role))) . ' · Organisation';
    @endphp

    {{-- Icon sprite: one definition shared by server-rendered and JS-rendered rows --}}
    <svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
        <symbol id="dp-i-view" viewBox="0 0 24 24">
            <path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12z" />
            <circle cx="12" cy="12" r="2.8" />
        </symbol>
        <symbol id="dp-i-edit" viewBox="0 0 24 24">
            <path d="M4 20h4L19.5 8.5a2.1 2.1 0 00-3-3L5 17v3z" />
            <path d="M14.5 7.5l3 3" />
        </symbol>
        <symbol id="dp-i-off" viewBox="0 0 24 24">
            <path d="M12 3v8" />
            <path d="M6.4 6.8a8 8 0 1011.2 0" />
        </symbol>
        <symbol id="dp-i-on" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="9" />
            <path d="M8 12.5l2.7 2.7L16 9.5" />
        </symbol>
    </svg>

    <div class="dp">

        <header class="dp-head dp-in" style="--i:0">
            <div>
                <p class="dp-eyebrow">{{ $eyebrow }}</p>
                <h1>Departments</h1>
                <p class="dp-sub">Create and maintain your company departments.</p>
            </div>
            @if ($can['create'])
                <button class="dp-btn dp-solid" type="button" data-dp-create>
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 5v14M5 12h14" />
                    </svg>Add department
                </button>
            @endif
        </header>

        <div class="dp-notice" id="dp-alert" role="status" aria-live="polite" hidden></div>

        {{-- Status switch + active share graph --}}
        <section class="dp-card dp-overview dp-in" style="--i:1" aria-label="Department status">
            <div class="dp-switch" role="group" aria-label="Filter departments by status">
                <a class="{{ $selectedStatus === 'active' ? 'is-on' : '' }}"
                    href="{{ route('portal.departments.index', ['status' => 'active']) }}"
                    @if ($selectedStatus === 'active') aria-current="true" @endif>Active <b
                        data-active-count>{{ $activeN }}</b></a>
                <a class="{{ $selectedStatus === 'inactive' ? 'is-on' : '' }}"
                    href="{{ route('portal.departments.index', ['status' => 'inactive']) }}"
                    @if ($selectedStatus === 'inactive') aria-current="true" @endif>Inactive <b
                        data-inactive-count>{{ $inactiveN }}</b></a>
            </div>
            <div class="dp-ratio">
                <div class="dp-ratio-top"><strong><span data-share>{{ $share }}</span>% active</strong><span>of all
                        departments</span></div>
                <div class="dp-rail" id="dp-ratio" style="--w: {{ $share }}%"><i></i></div>
            </div>
        </section>

        <section class="dp-card dp-in" style="--i:2" id="dp-panel" data-status="{{ e($selectedStatus) }}"
            data-can-view="{{ $can['view'] ? 1 : 0 }}" data-can-edit="{{ $can['edit'] ? 1 : 0 }}"
            data-can-delete="{{ $can['delete'] ? 1 : 0 }}" data-list-url="{{ route('portal.data.departments.index') }}"
            data-show-url="{{ route('portal.data.departments.show', '__department__') }}"
            data-update-url="{{ route('portal.data.departments.update', '__department__') }}"
            data-status-url="{{ route('portal.data.departments.status', '__department__') }}">

            <div class="dp-ph">
                <div>
                    <h2>Department list</h2>
                    <p><span data-dp-total>{{ number_format($departments->total()) }}</span> {{ $selectedStatus }}
                        departments</p>
                </div>
                <label class="dp-search" for="dp-search">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="11" cy="11" r="6.5" />
                        <path d="M16 16l4 4" />
                    </svg>
                    <input id="dp-search" type="search" placeholder="Search departments" aria-label="Search departments"
                        autocomplete="off">
                </label>
            </div>

            <div class="dp-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Department</th>
                            <th class="dp-md">Location</th>
                            <th class="dp-lg">Email</th>
                            <th class="dp-lg">Contact</th>
                            <th class="dp-md">Head</th>
                            <th>Status</th>
                            <th><span class="dp-sr">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody id="dp-rows" aria-live="polite">
                        @forelse ($departments as $department)
                            <tr>
                                <td><span class="dp-code">{{ $department->code }}</span></td>
                                <td>
                                    <div class="dp-name"><span
                                            class="dp-av dp-a{{ $department->id % 6 }}">{{ mb_strtoupper(mb_substr($department->name, 0, 1)) }}</span><strong>{{ $department->name }}</strong>
                                    </div>
                                </td>
                                <td class="dp-md">{{ $department->location }}</td>
                                <td class="dp-lg">{{ $department->email }}</td>
                                <td class="dp-lg">{{ $department->contact_no }}</td>
                                <td class="dp-md">{{ $department->head }}</td>
                                <td><span
                                        class="dp-status {{ $department->is_active ? 'is-active' : '' }}"><i></i>{{ $department->is_active ? 'Active' : 'Inactive' }}</span>
                                </td>
                                <td class="dp-actions">
                                    @if ($can['view'])
                                        <button class="dp-act is-view" type="button" title="View"
                                            aria-label="View {{ $department->name }}"
                                            data-dp-view="{{ $department->id }}"><svg class="dp-ico" aria-hidden="true">
                                                <use href="#dp-i-view" />
                                            </svg></button>
                                    @endif
                                    @if ($can['edit'])
                                        <button class="dp-act is-edit" type="button" title="Edit"
                                            aria-label="Edit {{ $department->name }}"
                                            data-dp-edit="{{ $department->id }}"><svg class="dp-ico" aria-hidden="true">
                                                <use href="#dp-i-edit" />
                                            </svg></button>
                                    @endif
                                    @if ($can['delete'])
                                        @php $label = $department->is_active ? 'Deactivate' : 'Activate'; @endphp
                                        <button class="dp-act {{ $department->is_active ? 'is-off' : 'is-on' }}"
                                            type="button" title="{{ $label }}"
                                            aria-label="{{ $label }} {{ $department->name }}"
                                            data-dp-status="{{ $department->id }}"
                                            data-next-status="{{ $department->is_active ? 0 : 1 }}"><svg class="dp-ico"
                                                aria-hidden="true">
                                                <use href="#dp-i-{{ $department->is_active ? 'off' : 'on' }}" />
                                            </svg></button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="dp-empty">No departments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="dp-foot">
                <span data-dp-summary>Showing {{ $departments->firstItem() ?: 0 }}–{{ $departments->lastItem() ?: 0 }} of
                    {{ number_format($departments->total()) }} departments</span>
                <div class="dp-pager">
                    <button class="dp-btn dp-ghost dp-sm" type="button" data-dp-prev
                        {{ $departments->currentPage() <= 1 ? 'disabled' : '' }}>Previous</button>
                    <span data-dp-page>Page {{ $departments->currentPage() }} of
                        {{ max(1, $departments->lastPage()) }}</span>
                    <button class="dp-btn dp-ghost dp-sm" type="button" data-dp-next
                        {{ $departments->currentPage() >= $departments->lastPage() ? 'disabled' : '' }}>Next</button>
                </div>
            </div>
        </section>
    </div>

    @if ($can['create'] || $can['edit'])
        <dialog class="dp-modal" id="dp-modal" aria-labelledby="dp-modal-title">
            <div class="dp-mhead">
                <div>
                    <p class="dp-eyebrow">{{ $eyebrow }}</p>
                    <h2 id="dp-modal-title">Add department</h2>
                    <p>Enter the required department information.</p>
                </div>
                <button class="dp-x" type="button" data-dp-close aria-label="Close dialog">×</button>
            </div>
            <form id="dp-form" data-store-url="{{ route('portal.data.departments.store') }}" novalidate>
                @csrf
                <div class="dp-grid">
                    <label>Department code
                        <input name="code" maxlength="16" required autocomplete="off" autocapitalize="characters"
                            spellcheck="false" placeholder="e.g. HR01" aria-describedby="dp-e-code">
                        <small class="dp-fe" id="dp-e-code" role="alert"></small>
                    </label>
                    <label>Department name
                        <input name="name" maxlength="100" required autocomplete="off"
                            placeholder="e.g. Human Resources" aria-describedby="dp-e-name">
                        <small class="dp-fe" id="dp-e-name" role="alert"></small>
                    </label>
                    <label>Location
                        <input name="location" maxlength="100" required autocomplete="off"
                            placeholder="e.g. Pune, Maharashtra" aria-describedby="dp-e-location">
                        <small class="dp-fe" id="dp-e-location" role="alert"></small>
                    </label>
                    <label>Email
                        <input name="email" type="email" maxlength="190" required autocomplete="off"
                            inputmode="email" spellcheck="false" placeholder="name@company.com"
                            aria-describedby="dp-e-email">
                        <small class="dp-fe" id="dp-e-email" role="alert"></small>
                    </label>
                    <label>Contact number
                        <input name="contact_no" type="tel" maxlength="10" minlength="10" pattern="[0-9]{10}"
                            required autocomplete="off" inputmode="numeric" placeholder="10 digits, e.g. 9876543210"
                            aria-describedby="dp-e-contact_no">
                        <small class="dp-fe" id="dp-e-contact_no" role="alert"></small>
                    </label>
                    <label>Department head
                        <input name="head" maxlength="120" required autocomplete="off"
                            placeholder="e.g. Priya Sharma" aria-describedby="dp-e-head">
                        <small class="dp-fe" id="dp-e-head" role="alert"></small>
                    </label>
                </div>
                <div class="dp-error" role="alert" hidden></div>
                <div class="dp-mfoot">
                    <button class="dp-btn dp-ghost" type="button" data-dp-close>Cancel</button>
                    <button class="dp-btn dp-solid" type="submit">Save department</button>
                </div>
            </form>
        </dialog>
    @endif

    @if ($can['view'])
        <dialog class="dp-modal" id="dp-view-modal" aria-labelledby="dp-view-title">
            <div class="dp-mhead">
                <div>
                    <p class="dp-eyebrow">{{ $eyebrow }}</p>
                    <h2 id="dp-view-title">Department details</h2>
                </div>
                <button class="dp-x" type="button" data-dp-close aria-label="Close dialog">×</button>
            </div>
            <dl class="dp-details" id="dp-details"></dl>
            <div class="dp-mfoot"><button class="dp-btn dp-ghost" type="button" data-dp-close>Close</button></div>
        </dialog>
    @endif

        <style @if ($nonce) nonce="{{ $nonce }}" @endif>
            .dp,
            .dp-modal {
                --g: var(--green, #15803d);
                --g-d: var(--green-dark, #166534);
                --g-l: var(--green-bg, #f0fdf4);
                --g-b: var(--green-border, #dcfce7);
                --ink: #171717;
                --mut: #737373;
                --line: var(--border, #e8e8e8);
                --soft: #fafafa;
            }

            .dp {
                max-width: 1280px;
                margin: 0 auto;
                padding: 8px 4px 40px;
                color: var(--ink);
                font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
                -webkit-font-smoothing: antialiased;
            }

            .dp *,
            .dp-modal *,
            .dp *::before {
                box-sizing: border-box;
            }

            .dp svg,
            .dp-modal svg,
            .dp-ico {
                fill: none;
                stroke: currentColor;
                stroke-width: 1.9;
                stroke-linecap: round;
                stroke-linejoin: round;
            }

            .dp-sr {
                position: absolute;
                width: 1px;
                height: 1px;
                overflow: hidden;
                clip: rect(0 0 0 0);
            }

            .dp-in {
                opacity: 0;
                transform: translateY(14px);
                animation: dp-rise .6s cubic-bezier(.22, 1, .36, 1) forwards;
                animation-delay: calc(var(--i) * 80ms);
            }

            @keyframes dp-rise {
                to {
                    opacity: 1;
                    transform: none;
                }
            }

            /* Heading + buttons */
            .dp-head {
                display: flex;
                flex-wrap: wrap;
                align-items: flex-end;
                justify-content: space-between;
                gap: 18px;
                margin-bottom: 22px;
            }

            .dp-eyebrow {
                margin: 0 0 6px;
                color: var(--g);
                font-size: 13px;
                font-weight: 700;
            }

            .dp-head h1 {
                margin: 0;
                font-size: clamp(28px, 4vw, 40px);
                line-height: 1.05;
                font-weight: 800;
                letter-spacing: -.03em;
            }

            .dp-sub {
                margin: 8px 0 0;
                color: var(--mut);
                font-size: 15px;
            }

            .dp-btn {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                min-height: 42px;
                padding: 0 18px;
                border-radius: 10px;
                border: 1px solid transparent;
                font: 700 14px/1 inherit;
                font-family: inherit;
                cursor: pointer;
                text-decoration: none;
                transition: transform .15s, background .15s, box-shadow .15s;
            }

            .dp-btn svg {
                width: 17px;
                height: 17px;
            }

            .dp-btn:active:not(:disabled) {
                transform: scale(.97);
            }

            .dp-btn:disabled {
                opacity: .5;
                cursor: not-allowed;
            }

            .dp-solid {
                color: #fff;
                background: var(--g);
                box-shadow: 0 6px 16px rgba(21, 128, 61, .22);
            }

            .dp-solid:hover:not(:disabled) {
                background: var(--g-d);
            }

            .dp-ghost {
                color: var(--ink);
                background: #fff;
                border-color: var(--line);
            }

            .dp-ghost:hover:not(:disabled) {
                background: var(--soft);
            }

            .dp-sm {
                min-height: 36px;
                padding: 0 14px;
                font-size: 13px;
            }

            .dp a:focus-visible,
            .dp button:focus-visible,
            .dp input:focus-visible,
            .dp-modal :focus-visible {
                outline: 2px solid #22c55e;
                outline-offset: 2px;
            }

            .dp-card {
                background: #fff;
                border: 1px solid var(--line);
                border-radius: 14px;
                box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
            }

            .dp-notice {
                margin-bottom: 16px;
                padding: 12px 16px;
                border-radius: 11px;
                font-size: 14px;
                font-weight: 600;
                animation: dp-rise .35s both;
            }

            .dp-notice.ok {
                color: var(--g-d);
                background: var(--g-l);
                border: 1px solid var(--g-b);
            }

            .dp-notice.bad {
                color: #991b1b;
                background: #fef2f2;
                border: 1px solid #fecaca;
            }

            /* Overview: segmented switch and active share graph */
            .dp-overview {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 20px;
                margin-bottom: 16px;
                padding: 16px 20px;
            }

            .dp-switch {
                display: inline-flex;
                padding: 4px;
                border-radius: 12px;
                background: #f3f4f3;
            }

            .dp-switch a {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 9px 16px;
                border-radius: 9px;
                color: var(--mut);
                font-size: 14px;
                font-weight: 700;
                text-decoration: none;
                transition: background .2s, color .2s, box-shadow .2s;
            }

            .dp-switch a:hover {
                color: var(--ink);
            }

            .dp-switch a.is-on {
                color: var(--g-d);
                background: #fff;
                box-shadow: 0 2px 8px rgba(15, 23, 42, .1);
            }

            .dp-switch b {
                min-width: 24px;
                padding: 2px 8px;
                border-radius: 999px;
                color: var(--mut);
                background: #e8ebe9;
                font-size: 12px;
                text-align: center;
                font-variant-numeric: tabular-nums;
            }

            .dp-switch .is-on b {
                color: #fff;
                background: var(--g);
            }

            .dp-ratio {
                flex: 1;
                min-width: 220px;
                max-width: 420px;
            }

            .dp-ratio-top {
                display: flex;
                justify-content: space-between;
                gap: 10px;
                font-size: 13px;
                color: var(--mut);
            }

            .dp-ratio-top strong {
                color: var(--ink);
                font-size: 15px;
                font-weight: 800;
            }

            .dp-rail {
                height: 10px;
                margin-top: 8px;
                overflow: hidden;
                border-radius: 999px;
                background: #eceeed;
            }

            .dp-rail i {
                display: block;
                width: var(--w);
                height: 100%;
                border-radius: inherit;
                background: linear-gradient(90deg, #22c55e, #15803d);
                transform-origin: left;
                transition: width .6s cubic-bezier(.22, 1, .36, 1);
                animation: dp-grow 1s cubic-bezier(.22, 1, .36, 1) .4s both;
            }

            @keyframes dp-grow {
                from {
                    transform: scaleX(0);
                }
            }

            /* List panel */
            .dp-ph {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 14px;
                padding: 20px 22px 16px;
            }

            .dp-ph h2 {
                margin: 0;
                font-size: 19px;
                font-weight: 800;
                letter-spacing: -.02em;
            }

            .dp-ph p {
                margin: 4px 0 0;
                color: var(--mut);
                font-size: 13px;
            }

            .dp-search {
                position: relative;
                display: block;
                width: min(320px, 100%);
            }

            .dp-search svg {
                position: absolute;
                left: 13px;
                top: 50%;
                width: 17px;
                height: 17px;
                margin-top: -8.5px;
                color: #a3a3a3;
                pointer-events: none;
            }

            .dp-search input,
            .dp-grid input {
                width: 100%;
                min-height: 42px;
                border: 1px solid var(--line);
                border-radius: 10px;
                color: var(--ink);
                background: #fff;
                font: 500 14px inherit;
                font-family: inherit;
                transition: border-color .15s, box-shadow .15s;
            }

            .dp-search input {
                padding: 0 14px 0 40px;
            }

            .dp-search input:focus,
            .dp-grid input:focus {
                border-color: #22c55e;
                box-shadow: 0 0 0 4px rgba(34, 197, 94, .14);
                outline: none;
            }

            .dp-scroll {
                overflow-x: auto;
            }

            .dp table {
                width: 100%;
                border-collapse: collapse;
                font-size: 13.5px;
            }

            .dp th {
                padding: 11px 16px;
                border-block: 1px solid var(--line);
                color: var(--mut);
                background: var(--soft);
                font-size: 12.5px;
                font-weight: 700;
                text-align: left;
                white-space: nowrap;
            }

            .dp td {
                padding: 13px 16px;
                border-bottom: 1px solid #f0f0f0;
                vertical-align: middle;
            }

            .dp tbody tr {
                transition: background .15s;
            }

            .dp tbody tr:hover {
                background: #fafcfa;
            }

            .dp tbody.is-loading {
                opacity: .45;
                pointer-events: none;
                transition: opacity .15s;
            }

            .dp-empty {
                padding: 44px 16px !important;
                color: var(--mut);
                text-align: center;
            }

            .dp-code {
                padding: 4px 9px;
                border-radius: 7px;
                color: var(--g-d);
                background: var(--g-l);
                border: 1px solid var(--g-b);
                font: 700 12px ui-monospace, SFMono-Regular, Menlo, monospace;
            }

            .dp-name {
                display: flex;
                align-items: center;
                gap: 11px;
                min-width: 150px;
            }

            .dp-name strong {
                font-weight: 700;
            }

            .dp-av {
                display: grid;
                place-items: center;
                flex: none;
                width: 34px;
                height: 34px;
                border-radius: 10px;
                font-size: 13px;
                font-weight: 800;
            }

            .dp-a0 {
                color: #2563eb;
                background: #eff6ff;
            }

            .dp-a1 {
                color: #7c3aed;
                background: #f5f3ff;
            }

            .dp-a2 {
                color: #ea580c;
                background: #fff7ed;
            }

            .dp-a3 {
                color: #15803d;
                background: #f0fdf4;
            }

            .dp-a4 {
                color: #0891b2;
                background: #ecfeff;
            }

            .dp-a5 {
                color: #4f46e5;
                background: #eef2ff;
            }

            .dp-status {
                display: inline-flex;
                align-items: center;
                gap: 7px;
                padding: 4px 11px;
                border-radius: 999px;
                color: #737373;
                background: #f3f4f3;
                font-size: 12px;
                font-weight: 700;
            }

            .dp-status i {
                width: 7px;
                height: 7px;
                border-radius: 50%;
                background: #a3a3a3;
            }

            .dp-status.is-active {
                color: var(--g-d);
                background: var(--g-l);
            }

            .dp-status.is-active i {
                background: #22c55e;
                box-shadow: 0 0 0 3px rgba(34, 197, 94, .18);
            }

            .dp-actions {
                white-space: nowrap;
                text-align: right;
            }

            .dp-act {
                display: inline-grid;
                place-items: center;
                width: 34px;
                height: 34px;
                padding: 0;
                border: 1px solid var(--line);
                border-radius: 9px;
                color: #525252;
                background: #fff;
                cursor: pointer;
                transition: color .15s, background .15s, border-color .15s, transform .15s;
            }

            .dp-act+.dp-act {
                margin-left: 6px;
            }

            .dp-ico {
                width: 17px;
                height: 17px;
                pointer-events: none;
            }

            .dp-act:active:not(:disabled) {
                transform: scale(.92);
            }

            .dp-act.is-view:hover {
                color: #2563eb;
                background: #eff6ff;
                border-color: #bfdbfe;
            }

            .dp-act.is-edit:hover,
            .dp-act.is-on:hover {
                color: var(--g-d);
                background: var(--g-l);
                border-color: var(--g-b);
            }

            .dp-act.is-off:hover {
                color: #b91c1c;
                background: #fef2f2;
                border-color: #fecaca;
            }

            .dp-act:disabled {
                opacity: .45;
                cursor: wait;
            }

            .dp-foot {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                padding: 14px 22px;
                color: var(--mut);
                font-size: 13px;
            }

            .dp-pager {
                display: flex;
                align-items: center;
                gap: 12px;
            }

            /* Modals (native dialog: focus trap and Escape come built in) */
            .dp-modal {
                width: min(640px, calc(100vw - 24px));
                max-height: calc(100dvh - 24px);
                padding: 24px;
                overflow-y: auto;
                border: 1px solid var(--line);
                border-radius: 18px;
                color: #171717;
                background: #fff;
                box-shadow: 0 30px 80px rgba(15, 23, 42, .28);
                font-family: Inter, ui-sans-serif, system-ui, sans-serif;
            }

            .dp-modal[open] {
                animation: dp-pop .28s cubic-bezier(.22, 1, .36, 1);
            }

            .dp-modal::backdrop {
                background: rgba(15, 23, 42, .42);
                backdrop-filter: blur(3px);
            }

            @keyframes dp-pop {
                from {
                    opacity: 0;
                    transform: translateY(14px) scale(.97);
                }
            }

            .dp-mhead {
                display: flex;
                justify-content: space-between;
                gap: 16px;
                margin-bottom: 20px;
            }

            .dp-mhead h2 {
                margin: 0;
                font-size: 22px;
                font-weight: 800;
                letter-spacing: -.02em;
            }

            .dp-mhead p {
                margin: 4px 0 0;
                color: var(--mut);
                font-size: 13.5px;
            }

            .dp-mhead .dp-eyebrow {
                margin: 0 0 4px;
                color: var(--g);
                font-size: 13px;
            }

            .dp-x {
                width: 34px;
                height: 34px;
                flex: none;
                border: 1px solid var(--line);
                border-radius: 9px;
                color: #525252;
                background: #fff;
                font-size: 20px;
                line-height: 1;
                cursor: pointer;
            }

            .dp-x:hover {
                color: var(--g);
                background: var(--g-l);
            }

            .dp-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 14px;
            }

            .dp-grid label {
                display: grid;
                gap: 6px;
                color: #404040;
                font-size: 13px;
                font-weight: 700;
            }

            .dp-grid input {
                padding: 0 13px;
            }

            .dp-grid input::placeholder {
                color: #a3a3a3;
                font-weight: 500;
            }

            .dp-grid input[aria-invalid="true"] {
                border-color: #ef4444;
                box-shadow: 0 0 0 4px rgba(239, 68, 68, .12);
            }

            .dp-fe {
                color: #b91c1c;
                font-size: 12px;
                font-weight: 600;
                line-height: 1.3;
            }

            .dp-fe:empty {
                display: none;
            }

            .dp-error {
                margin-top: 14px;
                padding: 11px 14px;
                border-radius: 10px;
                color: #991b1b;
                background: #fef2f2;
                border: 1px solid #fecaca;
                font-size: 13.5px;
                font-weight: 600;
            }

            .dp-mfoot {
                display: flex;
                justify-content: flex-end;
                gap: 10px;
                margin-top: 22px;
            }

            .dp-details {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
                margin: 0;
            }

            .dp-details div {
                padding: 12px 14px;
                border: 1px solid var(--line);
                border-radius: 11px;
                background: var(--soft);
            }

            .dp-details dt {
                color: var(--mut);
                font-size: 12px;
                font-weight: 600;
            }

            .dp-details dd {
                margin: 3px 0 0;
                font-size: 14.5px;
                font-weight: 700;
                overflow-wrap: anywhere;
            }

            @media (max-width: 1180px) {
                .dp-lg {
                    display: none;
                }
            }

            @media (max-width: 860px) {
                .dp-md {
                    display: none;
                }
            }

            @media (max-width: 560px) {

                .dp-grid,
                .dp-details {
                    grid-template-columns: 1fr;
                }

                .dp-head .dp-btn {
                    width: 100%;
                    justify-content: center;
                }

                .dp-ph,
                .dp-foot {
                    padding-inline: 16px;
                }
            }

            @media (prefers-reduced-motion: reduce) {

                .dp *,
                .dp-modal,
                .dp-in {
                    animation: none !important;
                    transition: none !important;
                    opacity: 1;
                    transform: none;
                }
            }
        </style>
    @push('scripts')
        <script @if ($nonce) nonce="{{ $nonce }}" @endif>
            (function() {
                'use strict';
                var $ = function(s, r) {
                    return (r || document).querySelector(s);
                };
                var panel = $('#dp-panel'),
                    rows = $('#dp-rows'),
                    form = $('#dp-form'),
                    formDlg = $('#dp-modal'),
                    viewDlg = $('#dp-view-modal'),
                    search = $('#dp-search'),
                    box = $('#dp-alert');
                var page = 1,
                    perPage = 10,
                    timer, noticeTimer, ctrl;
                var meta = $('meta[name="csrf-token"]'),
                    tok = form && form.querySelector('[name="_token"]');
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
                        signal: o.signal,
                        headers: {
                            Accept: 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: o.body ? JSON.stringify(o.body) : undefined
                    }).then(function(r) {
                        return r.json().catch(function() {
                            return {};
                        }).then(function(p) {
                            if (!r.ok) {
                                var k = p.errors ? Object.keys(p.errors) : [];
                                var e = new Error(k.length ? p.errors[k[0]][0] : p.message ||
                                    'The request failed.');
                                e.fields = p.errors || null;
                                throw e;
                            }
                            return p;
                        });
                    });
                }

                function notify(msg, bad) {
                    box.textContent = msg;
                    box.className = 'dp-notice ' + (bad ? 'bad' : 'ok');
                    box.hidden = false;
                    clearTimeout(noticeTimer);
                    noticeTimer = setTimeout(function() {
                        box.hidden = true;
                    }, 7000);
                }

                function open(d) {
                    if (d && d.showModal) d.showModal();
                    else if (d) d.setAttribute('open', '');
                }

                function close(d) {
                    if (d && d.close) d.close();
                    else if (d) d.removeAttribute('open');
                }

                function tpl(t, id) {
                    return t.replace('__department__', encodeURIComponent(id));
                }

                function counters(delta) {
                    var a = $('[data-active-count]'),
                        i = $('[data-inactive-count]');
                    if (delta) {
                        var from = delta > 0 ? i : a,
                            to = delta > 0 ? a : i;
                        from.textContent = Math.max(0, (+from.textContent || 0) - 1);
                        to.textContent = (+to.textContent || 0) + 1;
                    }
                    var x = +a.textContent || 0,
                        t = x + (+i.textContent || 0),
                        s = t ? Math.round(x / t * 100) : 0;
                    $('#dp-ratio').style.setProperty('--w', s + '%');
                    $('[data-share]').textContent = s;
                }

                var NS = 'http://www.w3.org/2000/svg',
                    XL = 'http://www.w3.org/1999/xlink';

                function icon(name) {
                    var svg = document.createElementNS(NS, 'svg'),
                        use = document.createElementNS(NS, 'use');
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
                    b.title = label;
                    b.setAttribute('aria-label', label + ' ' + who);
                    b.setAttribute(attr, value);
                    b.appendChild(icon(kind));
                    return b;
                }

                function cell(row, text, cls) {
                    var td = h('td', cls, text);
                    row.appendChild(td);
                    return td;
                }

                function render(payload) {
                    var list = Array.isArray(payload) ? payload : (payload && Array.isArray(payload.data) ? payload.data :
                        (payload && payload.data && Array.isArray(payload.data.data) ? payload.data.data : null));
                    var m = payload && payload.meta ? payload.meta : (payload && payload.data && payload.data.meta ? payload
                        .data.meta : payload);
                    if (!list || !m || typeof m.current_page !== 'number' || typeof m.last_page !== 'number' || typeof m
                        .total !== 'number') {
                        throw new Error('The department list response was invalid. Please refresh and try again.');
                    }
                    perPage = m.per_page || perPage;
                    rows.textContent = '';
                    list.forEach(function(d) {
                        var tr = h('tr');
                        cell(tr, null).appendChild(h('span', 'dp-code', d.code));
                        var name = h('div', 'dp-name');
                        name.appendChild(h('span', 'dp-av dp-a' + (Number(d.id) % 6), String(d.name || '?').charAt(
                            0).toUpperCase()));
                        name.appendChild(h('strong', null, d.name));
                        cell(tr, null).appendChild(name);
                        cell(tr, d.location, 'dp-md');
                        cell(tr, d.email, 'dp-lg');
                        cell(tr, d.contact_no, 'dp-lg');
                        cell(tr, d.head, 'dp-md');
                        var st = h('span', 'dp-status' + (d.is_active ? ' is-active' : ''));
                        st.appendChild(h('i'));
                        st.appendChild(document.createTextNode(d.is_active ? 'Active' : 'Inactive'));
                        cell(tr, null).appendChild(st);
                        var act = cell(tr, null, 'dp-actions');
                        if (panel.dataset.canView === '1' && viewDlg) act.appendChild(iconButton('view', 'View', d
                            .name, 'is-view', 'data-dp-view', d.id));
                        if (panel.dataset.canEdit === '1' && form) act.appendChild(iconButton('edit', 'Edit', d
                            .name, 'is-edit', 'data-dp-edit', d.id));
                        if (panel.dataset.canDelete === '1') {
                            var t = iconButton(d.is_active ? 'off' : 'on', d.is_active ? 'Deactivate' : 'Activate',
                                d.name, d.is_active ? 'is-off' : 'is-on', 'data-dp-status', d.id);
                            t.setAttribute('data-next-status', d.is_active ? '0' : '1');
                            act.appendChild(t);
                        }
                        rows.appendChild(tr);
                    });
                    if (!list.length) {
                        var tr = h('tr'),
                            td = h('td', 'dp-empty', 'No departments found.');
                        td.colSpan = 8;
                        tr.appendChild(td);
                        rows.appendChild(tr);
                    }
                    var from = m.total ? (m.from || (m.current_page - 1) * perPage + 1) : 0;
                    var to = m.total ? (m.to || Math.min(m.current_page * perPage, m.total)) : 0;
                    $('[data-dp-total]').textContent = Number(m.total).toLocaleString('en-IN');
                    $('[data-dp-summary]').textContent = 'Showing ' + from + '–' + to + ' of ' + Number(m.total)
                        .toLocaleString('en-IN') + ' departments';
                    $('[data-dp-page]').textContent = 'Page ' + m.current_page + ' of ' + Math.max(1, m.last_page);
                    $('[data-dp-prev]').disabled = m.current_page <= 1;
                    $('[data-dp-next]').disabled = m.current_page >= m.last_page;
                    page = m.current_page;
                }

                function load(target) {
                    if (ctrl && ctrl.abort) ctrl.abort();
                    ctrl = window.AbortController ? new AbortController() : null;
                    var url = new URL(panel.dataset.listUrl, window.location.origin);
                    url.searchParams.set('page', target || 1);
                    url.searchParams.set('search', search.value);
                    url.searchParams.set('status', panel.dataset.status);
                    rows.classList.add('is-loading');
                    return request(url.toString(), {
                            signal: ctrl ? ctrl.signal : undefined
                        }).then(render)
                        .catch(function(err) {
                            if (err.name !== 'AbortError') notify(err.message, true);
                        })
                        .then(function() {
                            rows.classList.remove('is-loading');
                        });
                }

                search.addEventListener('input', function() {
                    clearTimeout(timer);
                    timer = setTimeout(function() {
                        load(1);
                    }, 280);
                });
                $('[data-dp-prev]').addEventListener('click', function() {
                    load(Math.max(1, page - 1));
                });
                $('[data-dp-next]').addEventListener('click', function() {
                    load(page + 1);
                });

                var create = $('[data-dp-create]');
                if (create && form) create.addEventListener('click', function() {
                    form.reset();
                    delete form.dataset.departmentId;
                    clearErrors();
                    $('#dp-modal-title').textContent = 'Add department';
                    form.querySelector('[type="submit"]').textContent = 'Save department';
                    open(formDlg);
                });

                /* Field rules: same limits as the inputs; the server must enforce them again */
                var rules = {
                    code: {
                        re: /^[A-Za-z0-9_-]{2,16}$/,
                        msg: 'Use 2–16 letters, numbers, hyphen or underscore.',
                        clean: function(v) {
                            return v.toUpperCase().replace(/[^A-Z0-9_-]/g, '').slice(0, 16);
                        }
                    },
                    name: {
                        re: /^.{2,100}$/,
                        msg: 'Enter the department name (2–100 characters).'
                    },
                    location: {
                        re: /^.{2,100}$/,
                        msg: 'Enter the department location (2–100 characters).'
                    },
                    email: {
                        re: /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/,
                        msg: 'Enter a valid email address, like name@company.com.'
                    },
                    contact_no: {
                        re: /^[0-9]{10}$/,
                        msg: 'Enter exactly 10 digits. Letters and symbols are not allowed.',
                        clean: function(v) {
                            return v.replace(/\D/g, '').slice(0, 10);
                        }
                    },
                    head: {
                        re: /^[^0-9]{2,120}$/,
                        msg: 'Enter the head’s full name (2–120 characters, no numbers).'
                    }
                };

                function setError(name, msg) {
                    var input = form.elements[name],
                        out = $('#dp-e-' + name);
                    if (!input || !out) return;
                    out.textContent = msg || '';
                    if (msg) input.setAttribute('aria-invalid', 'true');
                    else input.removeAttribute('aria-invalid');
                }

                function clearErrors() {
                    Object.keys(rules).forEach(function(n) {
                        setError(n, '');
                    });
                    form.querySelector('.dp-error').hidden = true;
                }

                function check(name) {
                    var v = form.elements[name].value.trim();
                    var msg = !v ? 'This field is required.' : (rules[name].re.test(v) ? '' : rules[name].msg);
                    setError(name, msg);
                    return !msg;
                }
                if (form) Object.keys(rules).forEach(function(name) {
                    var input = form.elements[name];
                    input.addEventListener('input', function() {
                        if (rules[name].clean) {
                            var c = rules[name].clean(input.value);
                            if (c !== input.value) input.value = c;
                        }
                        if (input.getAttribute('aria-invalid')) check(name);
                    });
                    input.addEventListener('blur', function() {
                        if (input.value) check(name);
                    });
                });

                if (form) form.addEventListener('submit', function(ev) {
                    ev.preventDefault();
                    var bad = Object.keys(rules).filter(function(n) {
                        return !check(n);
                    });
                    if (bad.length) {
                        form.elements[bad[0]].focus();
                        return;
                    }
                    var data = {},
                        id = form.dataset.departmentId,
                        btn = form.querySelector('[type="submit"]'),
                        label = btn.textContent;
                    new FormData(form).forEach(function(v, k) {
                        if (k !== '_token') data[k] = typeof v === 'string' ? v.trim() : v;
                    });
                    btn.disabled = true;
                    btn.textContent = 'Saving…';
                    request(id ? tpl(panel.dataset.updateUrl, id) : form.dataset.storeUrl, {
                            method: id ? 'PUT' : 'POST',
                            body: data
                        })
                        .then(function(p) {
                            close(formDlg);
                            notify(p.message || 'Saved.', false);
                            if (!id) {
                                var a = $('[data-active-count]');
                                a.textContent = (+a.textContent || 0) + 1;
                                counters(0);
                            }
                            return load(1);
                        })
                        .catch(function(err) {
                            var mapped = [],
                                b = form.querySelector('.dp-error');
                            Object.keys(err.fields || {}).forEach(function(k) {
                                if (rules[k]) {
                                    setError(k, [].concat(err.fields[k])[0]);
                                    mapped.push(k);
                                }
                            });
                            if (mapped.length) {
                                b.hidden = true;
                                form.elements[mapped[0]].focus();
                                return;
                            }
                            b.textContent = err.message;
                            b.hidden = false;
                        })
                        .then(function() {
                            btn.disabled = false;
                            btn.textContent = label;
                        });
                });

                rows.addEventListener('click', function(ev) {
                    var vb = ev.target.closest('[data-dp-view]'),
                        eb = ev.target.closest('[data-dp-edit]'),
                        sb = ev.target.closest('[data-dp-status]');
                    if (vb || eb) {
                        request(tpl(panel.dataset.showUrl, (vb || eb).getAttribute(vb ? 'data-dp-view' :
                            'data-dp-edit'))).then(function(p) {
                            var d = p.data;
                            if (vb) {
                                var dl = $('#dp-details');
                                dl.textContent = '';
                                [
                                    ['Code', d.code],
                                    ['Name', d.name],
                                    ['Location', d.location],
                                    ['Email', d.email],
                                    ['Contact number', d.contact_no],
                                    ['Department head', d.head],
                                    ['Status', d.is_active ? 'Active' : 'Inactive']
                                ].forEach(function(it) {
                                    var r = h('div');
                                    r.appendChild(h('dt', null, it[0]));
                                    r.appendChild(h('dd', null, it[1] || '—'));
                                    dl.appendChild(r);
                                });
                                open(viewDlg);
                                return;
                            }
                            form.reset();
                            form.dataset.departmentId = d.id;
                            clearErrors();
                            Object.keys(d).forEach(function(k) {
                                if (form.elements[k] && k !== '_token') form.elements[k].value = d[
                                    k] == null ? '' : d[k];
                            });
                            $('#dp-modal-title').textContent = 'Edit department';
                            form.querySelector('[type="submit"]').textContent = 'Save changes';
                            open(formDlg);
                        }).catch(function(err) {
                            notify(err.message, true);
                        });
                    }
                    if (sb) {
                        var on = sb.dataset.nextStatus === '1',
                            word = on ? 'Activate' : 'Deactivate';
                        var ask = window.Swal ? window.Swal.fire({
                            icon: 'warning',
                            title: word + ' department?',
                            showCancelButton: true,
                            confirmButtonText: word,
                            cancelButtonText: 'Cancel',
                            text: on ? 'The department will be available for new employee records.' :
                                'The department stays in the system but is unavailable for new employee records.',
                            confirmButtonColor: on ? '#15803d' : '#b91c1c'
                        }).then(function(r) {
                            return r.isConfirmed;
                        }) : Promise.resolve(window.confirm(word + ' this department?'));
                        ask.then(function(ok) {
                            if (!ok) return;
                            sb.disabled = true;
                            request(tpl(panel.dataset.statusUrl, sb.dataset.dpStatus), {
                                    method: 'PATCH',
                                    body: {
                                        is_active: on ? 1 : 0
                                    }
                                })
                                .then(function(p) {
                                    counters(on ? 1 : -1);
                                    notify(p.message || 'Updated.', false);
                                    return load(page);
                                })
                                .catch(function(err) {
                                    notify(err.message, true);
                                })
                                .then(function() {
                                    sb.disabled = false;
                                });
                        });
                    }
                });

                [formDlg, viewDlg].forEach(function(d) {
                    if (!d) return;
                    d.addEventListener('click', function(ev) {
                        if (ev.target === d || ev.target.closest('[data-dp-close]')) close(d);
                    });
                });
            })();
        </script>
    @endpush
@endsection
