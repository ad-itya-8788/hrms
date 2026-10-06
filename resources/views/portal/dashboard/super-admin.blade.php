@extends('layouts.portal')

@section('content')
@php
    $nonce = function_exists('csp_nonce') ? csp_nonce() : null;
@endphp

<style @if ($nonce) nonce="{{ $nonce }}" @endif>
.sa-dashboard{--sa-ink:#152344;--sa-text:#34445f;--sa-muted:#7d8aa3;--sa-line:#e9edf5;--sa-green:#1fb875;--sa-blue:#3478ee;--sa-violet:#8057e7;--sa-amber:#f3a51b;--sa-red:#ef5961;max-width:1440px;margin:0 auto;padding:24px 24px 42px;color:var(--sa-text)}
.sa-dashboard *{box-sizing:border-box}
.sa-head{display:flex;align-items:flex-end;justify-content:space-between;gap:18px;margin:2px 0 21px}
.sa-kicker{margin:0 0 7px;color:#5573a8;font-size:11px;font-weight:800;letter-spacing:.1em;text-transform:uppercase}
.sa-head h1{margin:0;color:var(--sa-ink);font-size:clamp(27px,3vw,35px);line-height:1.12;font-weight:850;letter-spacing:-.035em}
.sa-head p:last-child{margin:7px 0 0;color:var(--sa-muted);font-size:14px}
.sa-head-side{display:flex;align-items:center;gap:9px}
.sa-date{display:inline-flex;align-items:center;gap:8px;padding:10px 12px;border:1px solid var(--sa-line);border-radius:11px;background:#fff;color:#53637f;font-size:12px;font-weight:700;white-space:nowrap}
.sa-date svg{width:16px;height:16px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.sa-refresh{display:inline-grid;width:39px;height:39px;place-items:center;border:1px solid var(--sa-line);border-radius:11px;background:#fff;color:#52617a;cursor:pointer;transition:.18s}
.sa-refresh:hover{border-color:#c9d7ee;background:#f6f9ff;color:var(--sa-blue)}
.sa-refresh:disabled{opacity:.55;cursor:wait}
.sa-refresh svg{width:16px;height:16px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
.sa-error{display:flex;align-items:center;justify-content:space-between;gap:14px;margin:0 0 16px;padding:12px 15px;border:1px solid #f3c4c6;border-radius:11px;background:#fff5f5;color:#a2343c;font-size:13px}
.sa-error[hidden]{display:none}
.sa-retry{border:0;background:transparent;color:#a2343c;font-size:12px;font-weight:700;font-family:inherit;text-decoration:underline;cursor:pointer}
.sa-metrics{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:13px;margin-bottom:17px}
.sa-metric{display:flex;align-items:center;gap:13px;min-width:0;min-height:94px;padding:15px 16px;border:1px solid #edf0f5;border-radius:14px;background:#fff;box-shadow:0 4px 14px rgba(28,52,93,.045)}
.sa-metric-icon{display:grid;width:54px;height:54px;flex:0 0 54px;place-items:center;border-radius:14px;background:var(--metric-tint,#eff4ff);color:var(--metric-color,var(--sa-blue))}
.sa-metric-icon svg{width:24px;height:24px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.sa-metric-copy{min-width:0}
.sa-metric-label{display:block;overflow:hidden;color:#60708e;font-size:11px;font-weight:650;text-overflow:ellipsis;white-space:nowrap}
.sa-metric-value{display:block;margin-top:5px;color:var(--sa-ink);font-size:28px;line-height:1;font-weight:850;letter-spacing:-.035em;font-variant-numeric:tabular-nums}
.sa-metric-note{margin:5px 0 0;color:#8793a8;font-size:10px}
.sa-section-title{display:flex;align-items:baseline;justify-content:space-between;gap:12px;margin:19px 1px 10px}
.sa-section-title h2{margin:0;color:var(--sa-ink);font-size:14px;font-weight:850}
.sa-section-title>span{color:var(--sa-muted);font-size:10px}
.sa-skeleton{color:transparent!important;border-radius:7px;background:linear-gradient(100deg,#edf1f8 25%,#f8faff 42%,#edf1f8 60%);background-size:200% 100%;animation:sa-shimmer 1.25s ease-in-out infinite}
@keyframes sa-shimmer{to{background-position-x:-200%}}
.sa-main-grid{display:grid;grid-template-columns:minmax(0,1.3fr) minmax(300px,.9fr);gap:15px;margin-bottom:16px}
.sa-panel{min-width:0;padding:17px 18px;border:1px solid #edf0f5;border-radius:15px;background:#fff;box-shadow:0 4px 14px rgba(28,52,93,.045)}
.sa-panel-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:14px}
.sa-panel-head h2{margin:0;color:var(--sa-ink);font-size:14px;font-weight:850}
.sa-panel-head p{margin:4px 0 0;color:var(--sa-muted);font-size:11px}
.sa-panel-link{color:var(--sa-blue);font-size:11px;font-weight:750;text-decoration:none;white-space:nowrap}
.sa-panel-link:hover{text-decoration:underline}
.sa-join-chart{display:grid;grid-template-columns:34px minmax(0,1fr);gap:8px;height:188px;padding:3px 0 0}
.sa-y-axis{display:flex;flex-direction:column;justify-content:space-between;padding:0 0 22px;color:#8793a8;font-size:9px;text-align:right}
.sa-plot{position:relative;display:flex;align-items:stretch;justify-content:space-around;gap:8px;padding:7px 6px 0;border-bottom:1px solid #e8edf5;background:repeating-linear-gradient(to bottom,transparent 0,transparent 24%,#eff2f7 24.5%,transparent 25%)}
.sa-month-col{display:flex;flex:1;min-width:0;flex-direction:column;align-items:center;justify-content:flex-end;gap:7px}
.sa-month-value{color:#61718f;font-size:9px;font-weight:700}
.sa-month-track{display:flex;width:min(25px,74%);height:138px;align-items:flex-end;overflow:hidden;border-radius:5px 5px 0 0}
.sa-month-bar{display:block;width:100%;height:0;min-height:0;border-radius:4px 4px 0 0;background:linear-gradient(180deg,#21c17b,#12a968);transition:height .85s cubic-bezier(.2,.8,.2,1)}
.sa-month-label{padding-bottom:7px;color:#71809a;font-size:10px}
.sa-donut-content{display:flex;align-items:center;justify-content:center;gap:23px;min-height:188px}
.sa-donut{position:relative;display:grid;width:154px;height:154px;flex:0 0 154px;place-items:center;border-radius:50%;background:conic-gradient(#3478ee 0deg 300deg,#1fb875 300deg 335deg,#f3b53d 335deg 360deg)}
.sa-donut:before{position:absolute;inset:15px;border-radius:50%;background:#fff;content:""}
.sa-donut-center{position:relative;z-index:1;text-align:center}
.sa-donut-total{display:block;color:var(--sa-ink);font-size:24px;font-weight:850;letter-spacing:-.04em}
.sa-donut-caption{display:block;margin-top:2px;color:#6d7b94;font-size:10px}
.sa-legend{display:grid;gap:11px;min-width:100px}
.sa-legend-row{display:grid;grid-template-columns:9px minmax(65px,1fr) auto;align-items:center;gap:7px;color:#65748e;font-size:10px}
.sa-legend-dot{width:8px;height:8px;border-radius:50%;background:var(--legend-color)}
.sa-legend-row strong{color:#40516e;font-size:10px;font-weight:750;white-space:nowrap}
.sa-activity-grid{display:grid;grid-template-columns:minmax(0,1.35fr) minmax(280px,.9fr);gap:15px}
.sa-table-wrap{overflow:auto}
.sa-table{width:100%;border-collapse:collapse;min-width:450px}
.sa-table th{padding:9px 9px;background:#f4f6fa;color:#53637f;font-size:10px;font-weight:800;text-align:left;white-space:nowrap}
.sa-table td{padding:9px;border-bottom:1px solid #edf0f5;color:#4e5e79;font-size:10px;vertical-align:middle}
.sa-table tr:last-child td{border-bottom:0}
.sa-person{display:flex;align-items:center;gap:8px;min-width:120px;color:#34445f;font-weight:700}
.sa-person-mark{display:grid;width:24px;height:24px;flex:0 0 24px;place-items:center;border-radius:50%;background:#eaf1ff;color:#2363ce;font-size:9px;font-weight:850}
.sa-status{display:inline-flex;align-items:center;padding:4px 8px;border-radius:5px;background:#fff2d6;color:#a56a00;font-size:9px;font-weight:800;white-space:nowrap}
.sa-status.is-approved{background:#dcf7e8;color:#14884e}
.sa-status.is-rejected{background:#ffe2e2;color:#cc353c}
.sa-status.is-completed{background:#e8eefe;color:#4561bd}
.sa-holidays{display:grid}
.sa-holiday-row{display:grid;grid-template-columns:minmax(100px,.75fr) minmax(0,1.25fr);gap:12px;padding:11px 4px;border-bottom:1px solid #edf0f5;color:#50617e;font-size:10px}
.sa-holiday-row:last-child{border-bottom:0}
.sa-holiday-date{color:#34445f;font-weight:750;white-space:nowrap}
.sa-holiday-name{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.sa-empty{padding:28px 12px;color:#8793a8;font-size:11px;text-align:center}
.sa-inventory{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:9px}
.sa-inventory-item{min-width:0;padding:12px 13px;border:1px solid #edf0f5;border-radius:11px;background:#fff}
.sa-inventory-item span{display:block;overflow:hidden;color:#75829a;font-size:9px;font-weight:700;text-overflow:ellipsis;text-transform:uppercase;white-space:nowrap}
.sa-inventory-item strong{display:block;margin-top:5px;color:#273958;font-size:17px;font-weight:850;font-variant-numeric:tabular-nums}
.sa-footer{margin-top:12px;color:#8a96aa;font-size:10px;text-align:right}
@media(max-width:1050px){.sa-inventory{grid-template-columns:repeat(4,minmax(0,1fr))}}
@media(max-width:850px){.sa-main-grid,.sa-activity-grid{grid-template-columns:1fr}.sa-metrics{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:560px){.sa-dashboard{padding:18px 12px 30px}.sa-head{align-items:flex-start;flex-direction:column}.sa-head-side{width:100%;justify-content:flex-end}.sa-metrics{gap:9px}.sa-metric{min-height:82px;padding:11px;gap:9px}.sa-metric-icon{width:42px;height:42px;flex-basis:42px;border-radius:12px}.sa-metric-icon svg{width:20px;height:20px}.sa-metric-value{font-size:23px}.sa-metric-label{font-size:10px}.sa-panel{padding:14px}.sa-donut-content{gap:14px}.sa-donut{width:132px;height:132px;flex-basis:132px}.sa-donut:before{inset:13px}.sa-inventory{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:370px){.sa-metric{align-items:flex-start;flex-direction:column}.sa-metric-value{margin-top:3px}.sa-donut-content{flex-direction:column}.sa-legend{width:100%;grid-template-columns:1fr 1fr}}
@media(prefers-reduced-motion:reduce){.sa-dashboard *, .sa-dashboard *:before,.sa-dashboard *:after{animation-duration:.01ms!important;animation-iteration-count:1!important;scroll-behavior:auto!important;transition-duration:.01ms!important}}
</style>

<main class="sa-dashboard" id="super-admin-dashboard"
    data-statistics-url="{{ $statisticsUrl }}"
    data-leaves-url="{{ route('portal.leaves.index') }}"
    data-holidays-url="{{ route('portal.holidays.index') }}">
    <header class="sa-head">
        <div>
            <p class="sa-kicker">HRMS <span aria-hidden="true">·</span> Dashboard</p>
            <h1>Welcome back, {{ auth()->user()->name }}</h1>
            <p>Here's what's happening in your organization today.</p>
        </div>
        <div class="sa-head-side">
            <span class="sa-date">
                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg>
                {{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}
            </span>
            <button class="sa-refresh" type="button" id="sa-refresh" aria-label="Refresh dashboard data" title="Refresh dashboard data">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 7v5h-5M4 17v-5h5"/><path d="M5.6 9a7 7 0 0111.8-2L20 12M4 12l2.6 5a7 7 0 0011.8-2"/></svg>
            </button>
        </div>
    </header>

    <div class="sa-error" id="sa-error" role="alert" hidden>
        <span>Dashboard data could not be loaded. Check your connection and try again.</span>
        <button class="sa-retry" type="button" id="sa-retry">Retry</button>
    </div>

    <section class="sa-metrics" id="sa-metrics" aria-label="Key organization statistics" aria-busy="true">
        <article class="sa-metric">
            <span class="sa-metric-icon" style="--metric-color:#3478ee;--metric-tint:#eaf1ff"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM20 8v6M23 11h-6"/></svg></span>
            <div class="sa-metric-copy"><span class="sa-metric-label">Total employees</span><strong class="sa-metric-value sa-skeleton" data-stat="employees">000</strong><p class="sa-metric-note">All employee records</p></div>
        </article>
        <article class="sa-metric">
            <span class="sa-metric-icon" style="--metric-color:#18a960;--metric-tint:#e7f8ef"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
            <div class="sa-metric-copy"><span class="sa-metric-label">Active employees</span><strong class="sa-metric-value sa-skeleton" data-stat="active_employees">000</strong><p class="sa-metric-note" data-note="active-rate">—% of employee records</p></div>
        </article>
        <article class="sa-metric">
            <span class="sa-metric-icon" style="--metric-color:#e99a16;--metric-tint:#fff4df"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2M5 4l-2 3M19 4l2 3"/></svg></span>
            <div class="sa-metric-copy"><span class="sa-metric-label">On leave</span><strong class="sa-metric-value sa-skeleton" data-stat="on_leave_employees">000</strong><p class="sa-metric-note">Employees on leave</p></div>
        </article>
        <article class="sa-metric">
            <span class="sa-metric-icon" style="--metric-color:#e95660;--metric-tint:#fff0f0"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg></span>
            <div class="sa-metric-copy"><span class="sa-metric-label">Pending leave requests</span><strong class="sa-metric-value sa-skeleton" data-stat="pending_leave_applications">000</strong><p class="sa-metric-note">Awaiting review</p></div>
        </article>
    </section>

    <section class="sa-main-grid" aria-label="Organization charts">
        <article class="sa-panel">
            <header class="sa-panel-head"><div><h2>Employee growth</h2><p>New joiners over the last 6 months</p></div></header>
            <div class="sa-join-chart" id="chart-joiners" aria-label="Monthly new employee counts" aria-live="polite">
                <div class="sa-y-axis"><span>—</span><span>—</span><span>—</span><span>—</span><span>0</span></div>
                <div class="sa-plot"><div class="sa-month-col"><span class="sa-month-value"> </span><span class="sa-month-track"><i class="sa-month-bar sa-skeleton"></i></span><span class="sa-month-label">···</span></div></div>
            </div>
        </article>
        <article class="sa-panel">
            <header class="sa-panel-head"><div><h2>Employee distribution</h2><p>Breakdown by employment status</p></div></header>
            <div class="sa-donut-content">
                <div class="sa-donut" id="employee-donut" role="img" aria-label="Employee status distribution">
                    <div class="sa-donut-center"><strong class="sa-donut-total sa-skeleton" data-stat="donut-total">000</strong><span class="sa-donut-caption">Employees</span></div>
                </div>
                <div class="sa-legend" id="employee-legend" aria-live="polite"><span class="sa-chart-empty">Loading…</span></div>
            </div>
        </article>
    </section>

    <section class="sa-activity-grid" aria-label="Recent leave requests and upcoming holidays">
        <article class="sa-panel">
            <header class="sa-panel-head">
                <div><h2>Recent leave requests</h2><p>The latest employee applications</p></div>
                <a class="sa-panel-link" href="{{ route('portal.leaves.index') }}">View all →</a>
            </header>
            <div class="sa-table-wrap">
                <table class="sa-table">
                    <thead><tr><th>Employee</th><th>Leave type</th><th>From</th><th>To</th><th>Status</th></tr></thead>
                    <tbody id="recent-leaves"><tr><td colspan="5" class="sa-empty">Loading requests…</td></tr></tbody>
                </table>
            </div>
        </article>
        <article class="sa-panel">
            <header class="sa-panel-head">
                <div><h2>Upcoming holidays</h2><p>Next holidays on the organization calendar</p></div>
                <a class="sa-panel-link" href="{{ route('portal.holidays.index') }}">View all →</a>
            </header>
            <div class="sa-holidays" id="upcoming-holidays"><div class="sa-empty">Loading holidays…</div></div>
        </article>
    </section>

    <div class="sa-section-title"><h2>All system records</h2><span>Live database totals</span></div>
    <section class="sa-inventory" id="sa-inventory" aria-label="All system record counts" aria-busy="true">
        @foreach ([
            'users' => 'User accounts',
            'departments' => 'Departments',
            'employee_types' => 'Employee types',
            'employee_roles' => 'Employee roles',
            'user_roles' => 'User roles',
            'documents' => 'Documents',
            'educations' => 'Education',
            'experiences' => 'Experience',
            'bank_details' => 'Bank profiles',
            'leave_applications' => 'Leave requests',
            'exit_passes' => 'Exit passes',
            'holidays' => 'Holidays',
            'modules' => 'Access modules',
        ] as $key => $label)
            <article class="sa-inventory-item"><span>{{ $label }}</span><strong class="sa-skeleton" data-stat="{{ $key }}">000</strong></article>
        @endforeach
    </section>
    <footer class="sa-footer" id="sa-updated">Loading live system data…</footer>
</main>

<script @if ($nonce) nonce="{{ $nonce }}" @endif>
(function () {
    'use strict';
    var root = document.getElementById('super-admin-dashboard');
    if (!root) { return; }
    var url = root.getAttribute('data-statistics-url');
    var errorBox = document.getElementById('sa-error');
    var refreshButton = document.getElementById('sa-refresh');
    var retryButton = document.getElementById('sa-retry');
    var reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var donutColors = ['#3478ee', '#1fb875', '#f3b53d', '#ef5961', '#8057e7', '#41b7c7'];

    function animateNumber(element, target) {
        var start = performance.now();
        var duration = reducedMotion ? 0 : 850;
        element.classList.remove('sa-skeleton');
        function frame(now) {
            var progress = duration ? Math.min(1, (now - start) / duration) : 1;
            var eased = 1 - Math.pow(1 - progress, 4);
            element.textContent = Math.round(target * eased).toLocaleString();
            if (progress < 1) { window.requestAnimationFrame(frame); }
        }
        window.requestAnimationFrame(frame);
    }

    function setCount(key, value) {
        var element = root.querySelector('[data-stat="' + key + '"]');
        if (element) { animateNumber(element, Number(value) || 0); }
    }

    function setNote(key, text) {
        var element = root.querySelector('[data-note="' + key + '"]');
        if (element) { element.textContent = text; }
    }

    function formatDate(value) {
        if (!value) { return '—'; }
        var date = new Date(value + 'T00:00:00');
        return isNaN(date.getTime()) ? '—' : date.toLocaleDateString([], { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function statusClass(status) {
        var key = String(status || '').toLowerCase().replace(/\s+/g, '-');
        return ['approved', 'rejected', 'completed'].indexOf(key) >= 0 ? ' is-' + key : '';
    }

    function renderJoiners(rows) {
        var container = document.getElementById('chart-joiners');
        container.textContent = '';
        var max = Math.max.apply(null, rows.map(function (row) { return Number(row.value) || 0; }).concat([1]));
        var axis = document.createElement('div');
        axis.className = 'sa-y-axis';
        [max, Math.ceil(max * .75), Math.ceil(max * .5), Math.ceil(max * .25), 0].forEach(function (value) {
            var tick = document.createElement('span');
            tick.textContent = Number(value).toLocaleString();
            axis.appendChild(tick);
        });
        var plot = document.createElement('div');
        plot.className = 'sa-plot';
        rows.forEach(function (row, index) {
            var column = document.createElement('div');
            column.className = 'sa-month-col';
            var value = document.createElement('span');
            value.className = 'sa-month-value';
            value.textContent = Number(row.value).toLocaleString();
            var track = document.createElement('span');
            track.className = 'sa-month-track';
            var bar = document.createElement('i');
            bar.className = 'sa-month-bar';
            track.appendChild(bar);
            var label = document.createElement('span');
            label.className = 'sa-month-label';
            label.textContent = row.label;
            column.appendChild(value);
            column.appendChild(track);
            column.appendChild(label);
            plot.appendChild(column);
            window.requestAnimationFrame(function () {
                bar.style.height = row.value ? Math.max(4, Number(row.value) * 100 / max) + '%' : '0%';
                bar.style.transitionDelay = (index * 70) + 'ms';
            });
        });
        container.appendChild(axis);
        container.appendChild(plot);
    }

    function renderDistribution(rows, total) {
        var donut = document.getElementById('employee-donut');
        var legend = document.getElementById('employee-legend');
        legend.textContent = '';
        setCount('donut-total', total);
        var populated = rows.filter(function (row) { return Number(row.value) > 0; });
        if (!populated.length || !total) {
            donut.style.background = '#e9edf5';
            var empty = document.createElement('span');
            empty.className = 'sa-chart-empty';
            empty.textContent = 'No employees';
            legend.appendChild(empty);
            return;
        }
        var cursor = 0;
        var stops = [];
        populated.forEach(function (row, index) {
            var end = cursor + Number(row.value) * 360 / total;
            stops.push(donutColors[index % donutColors.length] + ' ' + cursor + 'deg ' + end + 'deg');
            cursor = end;
            var item = document.createElement('div');
            item.className = 'sa-legend-row';
            var dot = document.createElement('i');
            dot.className = 'sa-legend-dot';
            dot.style.setProperty('--legend-color', donutColors[index % donutColors.length]);
            var name = document.createElement('span');
            name.textContent = row.label;
            var amount = document.createElement('strong');
            var percentage = Math.round(Number(row.value) * 100 / total);
            amount.textContent = Number(row.value).toLocaleString() + ' (' + percentage + '%)';
            item.appendChild(dot);
            item.appendChild(name);
            item.appendChild(amount);
            legend.appendChild(item);
        });
        donut.style.background = 'conic-gradient(' + stops.join(',') + ')';
    }

    function renderLeaves(rows) {
        var body = document.getElementById('recent-leaves');
        body.textContent = '';
        if (!rows.length) {
            var emptyRow = document.createElement('tr');
            var emptyCell = document.createElement('td');
            emptyCell.colSpan = 5;
            emptyCell.className = 'sa-empty';
            emptyCell.textContent = 'No leave requests yet.';
            emptyRow.appendChild(emptyCell);
            body.appendChild(emptyRow);
            return;
        }
        rows.forEach(function (row) {
            var tr = document.createElement('tr');
            var employeeCell = document.createElement('td');
            var person = document.createElement('span');
            person.className = 'sa-person';
            var initials = document.createElement('i');
            initials.className = 'sa-person-mark';
            initials.textContent = row.employee.split(/\s+/).map(function (part) { return part.charAt(0); }).slice(0, 2).join('').toUpperCase();
            var name = document.createElement('span');
            name.textContent = row.employee;
            person.appendChild(initials);
            person.appendChild(name);
            employeeCell.appendChild(person);
            tr.appendChild(employeeCell);
            [row.leave_type, formatDate(row.from_date), formatDate(row.to_date)].forEach(function (value) {
                var td = document.createElement('td');
                td.textContent = value || '—';
                tr.appendChild(td);
            });
            var statusCell = document.createElement('td');
            var status = document.createElement('span');
            status.className = 'sa-status' + statusClass(row.status);
            status.textContent = row.status;
            statusCell.appendChild(status);
            tr.appendChild(statusCell);
            body.appendChild(tr);
        });
    }

    function renderHolidays(rows) {
        var container = document.getElementById('upcoming-holidays');
        container.textContent = '';
        if (!rows.length) {
            var empty = document.createElement('div');
            empty.className = 'sa-empty';
            empty.textContent = 'No upcoming holidays scheduled.';
            container.appendChild(empty);
            return;
        }
        rows.forEach(function (holiday) {
            var item = document.createElement('div');
            item.className = 'sa-holiday-row';
            var date = document.createElement('span');
            date.className = 'sa-holiday-date';
            date.textContent = formatDate(holiday.date);
            var name = document.createElement('span');
            name.className = 'sa-holiday-name';
            name.textContent = holiday.name;
            item.appendChild(date);
            item.appendChild(name);
            container.appendChild(item);
        });
    }

    function displayData(data) {
        var totals = data.totals;
        [
            'users', 'employees', 'active_employees', 'on_leave_employees',
            'pending_leave_applications', 'departments', 'employee_types', 'employee_roles',
            'user_roles', 'documents', 'educations', 'experiences', 'bank_details',
            'leave_applications', 'exit_passes', 'holidays', 'modules'
        ].forEach(function (key) { setCount(key, totals[key]); });
        setNote('active-rate', totals.active_rate + '% of employee records');
        renderJoiners(data.charts.monthly_joiners || []);
        renderDistribution(data.charts.employee_statuses || [], totals.employees);
        renderLeaves(data.recent_leave_requests || []);
        renderHolidays(data.upcoming_holidays || []);
        document.getElementById('sa-updated').textContent = 'Updated ' + new Date(data.generated_at).toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
        errorBox.hidden = true;
        document.getElementById('sa-metrics').setAttribute('aria-busy', 'false');
        document.getElementById('sa-inventory').setAttribute('aria-busy', 'false');
    }

    function loadStatistics() {
        refreshButton.disabled = true;
        errorBox.hidden = true;
        fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (response) {
            return response.json().then(function (data) {
                if (!response.ok || !data.success) { throw new Error(data.message || 'Dashboard request failed.'); }
                return data;
            });
        })
        .then(displayData)
        .catch(function (error) {
            errorBox.hidden = false;
            document.getElementById('sa-updated').textContent = 'Unable to load data';
            console.error('Super Admin dashboard request failed:', error);
        })
        .then(function () { refreshButton.disabled = false; });
    }

    refreshButton.addEventListener('click', loadStatistics);
    retryButton.addEventListener('click', loadStatistics);
    loadStatistics();
})();
</script>
@endsection
