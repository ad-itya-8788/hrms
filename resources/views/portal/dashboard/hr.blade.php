@extends('layouts.portal')

@section('content')
@php
    $nonce = function_exists('csp_nonce') ? csp_nonce() : null;
    $date = \Carbon\Carbon::parse($dashboard['date'] ?? now());
    $maxDepartmentEmployees = max(1, (int) $dashboard['departments']->max('employees_count'));
@endphp

<style @if ($nonce) nonce="{{ $nonce }}" @endif>
.hr-dashboard{--hr-ink:#172644;--hr-text:#40516e;--hr-muted:#7c8aa3;--hr-line:#e7ecf3;--hr-blue:#316fe8;--hr-teal:#16a67a;--hr-amber:#e99b20;--hr-red:#d9515a;max-width:1400px;margin:0 auto;padding:25px 24px 40px;color:var(--hr-text)}
.hr-dashboard *{box-sizing:border-box}
.hr-head{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;margin:1px 0 22px}
.hr-kicker{display:flex;align-items:center;gap:8px;margin:0 0 8px;color:#54709e;font-size:11px;font-weight:800;letter-spacing:.1em;text-transform:uppercase}
.hr-kicker i{width:7px;height:7px;border-radius:50%;background:#20ba7d;box-shadow:0 0 0 4px #e5f7ef}
.hr-head h1{margin:0;color:var(--hr-ink);font-size:clamp(27px,3vw,36px);font-weight:850;line-height:1.1;letter-spacing:-.035em}
.hr-head p:last-child{margin:8px 0 0;color:var(--hr-muted);font-size:14px}
.hr-head-actions{display:flex;align-items:center;gap:9px}
.hr-today{padding:10px 12px;border:1px solid var(--hr-line);border-radius:10px;background:#fff;color:#586985;font-size:12px;font-weight:700;white-space:nowrap}
.hr-action{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:39px;padding:0 13px;border:1px solid #d8e4f8;border-radius:10px;background:#f5f8ff;color:#2f66cd;font-size:12px;font-weight:750;text-decoration:none;transition:.18s}
.hr-action:hover{border-color:#b9ccef;background:#edf3ff;color:#214fa8;text-decoration:none}
.hr-action.primary{border-color:var(--hr-blue);background:var(--hr-blue);color:white}
.hr-action.primary:hover{border-color:#235bc9;background:#235bc9;color:white}
.hr-action svg{width:15px;height:15px;fill:none;stroke:currentColor;stroke-width:1.9;stroke-linecap:round;stroke-linejoin:round}
.hr-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:13px;margin-bottom:16px}
.hr-profile{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:18px;margin-bottom:15px;padding:18px;border:1px solid var(--hr-line);border-radius:14px;background:linear-gradient(120deg,#fff,#f7f9ff);box-shadow:0 4px 15px rgba(26,48,83,.04)}
.hr-profile-identity{display:flex;align-items:center;gap:13px;min-width:0}
.hr-profile-avatar{display:grid;width:48px;height:48px;flex:0 0 48px;place-items:center;border-radius:14px;background:#eaf1ff;color:#3269d1;font-size:15px;font-weight:850}
.hr-profile-identity h2{margin:0;color:var(--hr-ink);font-size:14px;font-weight:850}
.hr-profile-identity p{margin:4px 0 0;color:var(--hr-muted);font-size:11px}
.hr-profile-details{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:9px 18px;margin:0}
.hr-profile-details div{min-width:100px}
.hr-profile-details dt{color:#8a96aa;font-size:9px;font-weight:750;text-transform:uppercase}
.hr-profile-details dd{margin:4px 0 0;color:#435471;font-size:10px;font-weight:700}
.hr-kpi{position:relative;display:flex;align-items:center;gap:13px;min-width:0;min-height:108px;padding:17px;border:1px solid var(--hr-line);border-radius:14px;background:#fff;box-shadow:0 4px 15px rgba(26,48,83,.045)}
.hr-kpi-icon{display:grid;width:49px;height:49px;flex:0 0 49px;place-items:center;border-radius:14px;background:var(--icon-bg);color:var(--icon-color)}
.hr-kpi-icon svg{width:23px;height:23px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.hr-kpi-content{min-width:0}
.hr-kpi-label{display:block;overflow:hidden;color:#6c7b95;font-size:11px;font-weight:700;text-overflow:ellipsis;white-space:nowrap}
.hr-kpi-value{display:block;margin-top:5px;color:var(--hr-ink);font-size:29px;line-height:1;font-weight:850;letter-spacing:-.04em;font-variant-numeric:tabular-nums}
.hr-kpi-foot{margin:5px 0 0;color:#8a96aa;font-size:10px}
.hr-grid{display:grid;grid-template-columns:minmax(0,1.05fr) minmax(0,1.4fr);gap:15px;margin-bottom:15px}
.hr-grid.hr-single-grid{grid-template-columns:1fr}
.hr-panel{min-width:0;padding:18px;border:1px solid var(--hr-line);border-radius:14px;background:#fff;box-shadow:0 4px 15px rgba(26,48,83,.04)}
.hr-panel-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:15px}
.hr-panel-head h2{margin:0;color:var(--hr-ink);font-size:14px;font-weight:850}
.hr-panel-head p{margin:5px 0 0;color:var(--hr-muted);font-size:11px}
.hr-panel-link{color:var(--hr-blue);font-size:11px;font-weight:750;text-decoration:none;white-space:nowrap}
.hr-panel-link:hover{text-decoration:underline}
.hr-departments{display:grid;gap:15px}
.hr-dept-row{min-width:0}
.hr-dept-meta{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:7px;color:#52627d;font-size:11px}
.hr-dept-meta strong{color:var(--hr-ink);font-size:11px}
.hr-dept-rail{height:7px;overflow:hidden;border-radius:99px;background:#edf1f7}
.hr-dept-rail i{display:block;height:100%;border-radius:inherit;background:linear-gradient(90deg,#3174ed,#70a0ff)}
.hr-empty{padding:25px 10px;color:#8793a8;font-size:11px;text-align:center}
.hr-table-wrap{overflow:auto}
.hr-table{width:100%;border-collapse:collapse;min-width:400px}
.hr-table th{padding:9px 8px;background:#f5f7fa;color:#65738c;font-size:10px;font-weight:800;text-align:left;white-space:nowrap}
.hr-table td{padding:10px 8px;border-bottom:1px solid #edf0f4;color:#55647d;font-size:10px;vertical-align:middle}
.hr-table tr:last-child td{border-bottom:0}
.hr-person{display:flex;align-items:center;gap:8px;min-width:140px;color:#34445f;font-weight:750}
.hr-avatar{display:grid;width:29px;height:29px;flex:0 0 29px;place-items:center;border-radius:9px;background:#edf3ff;color:#3269d1;font-size:9px;font-weight:850}
.hr-person-name{display:block;overflow:hidden;max-width:190px;text-overflow:ellipsis;white-space:nowrap}
.hr-person-sub{display:block;margin-top:2px;color:#8995a9;font-size:9px;font-weight:500}
.hr-tag{display:inline-flex;padding:4px 8px;border-radius:99px;background:#e8f7ef;color:#188354;font-size:9px;font-weight:800;white-space:nowrap}
.hr-tag.leave{background:#fff3dc;color:#a36905}
.hr-approval-form{display:flex;align-items:center;gap:5px}
.hr-approval-form button{padding:5px 8px;border:1px solid #cfe9da;border-radius:6px;background:#effaf4;color:#167749;font-size:9px;font-weight:700;font-family:inherit;cursor:pointer}
.hr-approval-form button.reject{border-color:#f0d0d0;background:#fff4f4;color:#b43e45}
.hr-approval-form button:hover{filter:brightness(.97)}
.hr-employee-link{color:inherit;text-decoration:none}
.hr-employee-link:hover{color:var(--hr-blue);text-decoration:underline}
.hr-holiday-list{display:grid}
.hr-holiday{display:flex;align-items:center;gap:12px;padding:11px 0;border-bottom:1px solid #edf0f4}
.hr-holiday:last-child{border-bottom:0}
.hr-holiday-date{display:grid;width:42px;height:45px;flex:0 0 42px;place-content:center;border:1px solid #e0e9fb;border-radius:10px;background:#f4f7ff;color:#3269d1;text-align:center}
.hr-holiday-date strong{font-size:15px;line-height:1}
.hr-holiday-date span{margin-top:3px;font-size:9px;font-weight:750;text-transform:uppercase}
.hr-holiday-info{min-width:0}
.hr-holiday-info strong{display:block;overflow:hidden;color:#34445f;font-size:11px;text-overflow:ellipsis;white-space:nowrap}
.hr-holiday-info small{display:block;margin-top:4px;color:#8793a8;font-size:10px}
.hr-restricted{padding:16px;border:1px dashed #dce3ee;border-radius:11px;background:#fafbfd;color:#78869c;font-size:11px;line-height:1.6}
@media(max-width:980px){.hr-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.hr-grid{grid-template-columns:1fr}}
@media(max-width:600px){.hr-dashboard{padding:18px 12px 30px}.hr-head{align-items:flex-start;flex-direction:column}.hr-head-actions{width:100%;flex-wrap:wrap}.hr-today{margin-right:auto}.hr-kpi{min-height:91px;padding:12px;gap:9px}.hr-kpi-icon{width:41px;height:41px;flex-basis:41px;border-radius:12px}.hr-kpi-icon svg{width:20px;height:20px}.hr-kpi-value{font-size:25px}.hr-kpi-label{font-size:10px}.hr-panel{padding:14px}}
@media(max-width:600px){.hr-profile{grid-template-columns:1fr}.hr-profile-details{justify-content:flex-start}}
@media(max-width:370px){.hr-kpis{grid-template-columns:1fr}.hr-kpi{min-height:78px}}
@media(prefers-reduced-motion:reduce){.hr-dashboard *{scroll-behavior:auto!important;transition-duration:.01ms!important}}
</style>

<main class="hr-dashboard">
    <header class="hr-head">
        <div>
            <p class="hr-kicker"><i aria-hidden="true"></i> People operations · HR workspace</p>
            <h1>HR dashboard</h1>
            <p>Good {{ $date->hour < 12 ? 'morning' : ($date->hour < 17 ? 'afternoon' : 'evening') }}, {{ auth()->user()->name }}. Here is your people operations overview.</p>
        </div>
        <div class="hr-head-actions">
            <span class="hr-today">{{ $date->format('l, d M Y') }}</span>
            @if ($dashboard['can_view_employees'])
                <a class="hr-action" href="{{ route('portal.employees.index') }}">Employee directory</a>
                @if ($dashboard['can_create_employees'])
                    <a class="hr-action primary" href="{{ route('portal.employees.create') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                        Add employee
                    </a>
                @endif
            @endif
        </div>
    </header>

    @if ($dashboard['can_view_employees'] || $dashboard['can_view_leaves'])
    <section class="hr-kpis" aria-label="HR summary">
        @if ($dashboard['can_view_employees'])
            <article class="hr-kpi">
                <span class="hr-kpi-icon" style="--icon-bg:#edf3ff;--icon-color:#3471df"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM20 8v6M23 11h-6"/></svg></span>
                <div class="hr-kpi-content"><span class="hr-kpi-label">Total employees</span><strong class="hr-kpi-value">{{ number_format($dashboard['total_employees']) }}</strong><p class="hr-kpi-foot">All employee records</p></div>
            </article>
            <article class="hr-kpi">
                <span class="hr-kpi-icon" style="--icon-bg:#e8f8f0;--icon-color:#169864"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.5 2.5L16.5 9"/></svg></span>
                <div class="hr-kpi-content"><span class="hr-kpi-label">Active employees</span><strong class="hr-kpi-value">{{ number_format($dashboard['active_employees']) }}</strong><p class="hr-kpi-foot">Currently active workforce</p></div>
            </article>
            <article class="hr-kpi">
                <span class="hr-kpi-icon" style="--icon-bg:#fff4df;--icon-color:#d58b16"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
                <div class="hr-kpi-content"><span class="hr-kpi-label">On leave</span><strong class="hr-kpi-value">{{ number_format($dashboard['on_leave_employees']) }}</strong><p class="hr-kpi-foot">Employees currently on leave</p></div>
            </article>
        @endif
        @if ($dashboard['can_view_leaves'])
            <article class="hr-kpi">
                <span class="hr-kpi-icon" style="--icon-bg:#fff0f0;--icon-color:#d9515a"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 3h8M9 3v5l-5 9a3 3 0 002.6 4.5h10.8A3 3 0 0020 17l-5-9V3M8 14h8"/></svg></span>
                <div class="hr-kpi-content"><span class="hr-kpi-label">Pending leave requests</span><strong class="hr-kpi-value">{{ number_format($dashboard['pending_leaves']) }}</strong><p class="hr-kpi-foot">Requests awaiting review</p></div>
            </article>
        @elseif ($dashboard['can_view_employees'])
            <article class="hr-kpi">
                <span class="hr-kpi-icon" style="--icon-bg:#f1edff;--icon-color:#8057d9"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V5M4 19h17M8 15l4-4 3 2 5-6"/></svg></span>
                <div class="hr-kpi-content"><span class="hr-kpi-label">New this month</span><strong class="hr-kpi-value">{{ number_format($dashboard['new_hires_this_month']) }}</strong><p class="hr-kpi-foot">Employees who joined this month</p></div>
            </article>
        @endif
        @if ($dashboard['can_view_exit_passes'])
            <article class="hr-kpi">
                <span class="hr-kpi-icon" style="--icon-bg:#f1edff;--icon-color:#8057d9"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 3h12l4 4v14H4zM8 11h8M8 15h5"/><path d="M16 3v5h4"/></svg></span>
                <div class="hr-kpi-content"><span class="hr-kpi-label">Pending exit passes</span><strong class="hr-kpi-value">{{ number_format($dashboard['pending_exit_passes']) }}</strong><p class="hr-kpi-foot">Awaiting department review</p></div>
            </article>
        @endif
    </section>
    @endif

    @if ($dashboard['is_department_head_employee'] && $dashboard['employee_profile'])
        @php($profile = $dashboard['employee_profile'])
        <section class="hr-profile" aria-label="Your employee profile">
            <div class="hr-profile-identity">
                <span class="hr-profile-avatar">{{ mb_substr($profile->first_name, 0, 1) }}{{ mb_substr($profile->last_name, 0, 1) }}</span>
                <div>
                    <h2>{{ $profile->full_name }}</h2>
                    <p>{{ optional($profile->employeeRole)->name ?: 'HR Operations Specialist' }} · {{ optional($profile->department)->name ?: 'Department not assigned' }}</p>
                </div>
            </div>
            <dl class="hr-profile-details">
                <div><dt>Employee ID</dt><dd>{{ $profile->employee_code ?: '—' }}</dd></div>
                <div><dt>Employment status</dt><dd>{{ ucwords(str_replace('_', ' ', $profile->employment_status ?: 'Not recorded')) }}</dd></div>
                <div><dt>Joining date</dt><dd>{{ $profile->joining_date ? $profile->joining_date->format('d M Y') : '—' }}</dd></div>
            </dl>
        </section>
    @endif

    @if ($dashboard['can_view_departments'] || $dashboard['can_view_employees'])
    <section class="hr-grid" aria-label="Department and employee activity">
        <article class="hr-panel">
            <header class="hr-panel-head">
                <div><h2>Department overview</h2><p>Active teams and their current workforce</p></div>
                @if ($dashboard['can_view_departments'])
                    <a class="hr-panel-link" href="{{ route('portal.departments.index') }}">Manage teams →</a>
                @endif
            </header>
            @if ($dashboard['can_view_departments'])
                <div class="hr-departments">
                    @forelse ($dashboard['departments'] as $department)
                        <div class="hr-dept-row">
                            <div class="hr-dept-meta">
                                <span>{{ $department->name }}</span>
                                @if ($dashboard['can_view_employees'])
                                    <strong>{{ number_format($department->employees_count) }}</strong>
                                @endif
                            </div>
                            @if ($dashboard['can_view_employees'])
                                <div class="hr-dept-rail"><i style="width:{{ min(100, $department->employees_count / $maxDepartmentEmployees * 100) }}%"></i></div>
                            @endif
                        </div>
                    @empty
                        <p class="hr-empty">No active departments have been added yet.</p>
                    @endforelse
                </div>
            @else
                <div class="hr-restricted">Department information is not available for this account.</div>
            @endif
        </article>

        <article class="hr-panel">
            <header class="hr-panel-head">
                <div><h2>Department employees</h2><p>Employee records in your department</p></div>
                @if ($dashboard['can_view_employees'])
                    <a class="hr-panel-link" href="{{ route('portal.employees.index') }}">View employees →</a>
                @endif
            </header>
            @if ($dashboard['can_view_employees'])
                <div class="hr-table-wrap">
                    <table class="hr-table">
                        <thead><tr><th>Employee</th><th>Department</th><th>Joined</th><th>Status</th></tr></thead>
                        <tbody>
                        @forelse ($dashboard['recent_employees'] as $employee)
                            <tr>
                                <td>
                                    <span class="hr-person">
                                        <i class="hr-avatar">{{ mb_substr($employee->first_name, 0, 1) }}{{ mb_substr($employee->last_name, 0, 1) }}</i>
                                        <span><a class="hr-person-name hr-employee-link" href="{{ route('portal.employees.show', $employee->id) }}">{{ $employee->full_name }}</a><small class="hr-person-sub">{{ $employee->employee_code }}</small></span>
                                    </span>
                                </td>
                                <td>{{ optional($employee->department)->name ?: '—' }}</td>
                                <td>{{ $employee->joining_date ? $employee->joining_date->format('d M Y') : '—' }}</td>
                                <td><span class="hr-tag">{{ ucwords(str_replace('_', ' ', $employee->employment_status)) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="hr-empty">No employee records found.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                <div class="hr-restricted">Employee records are not available for this account.</div>
            @endif
        </article>
    </section>
    @endif

    @if ($dashboard['can_view_leaves'] || $dashboard['can_view_exit_passes'] || $dashboard['can_view_holidays'])
    <section class="hr-grid {{ (int) $dashboard['can_view_leaves'] + (int) $dashboard['can_view_exit_passes'] + (int) $dashboard['can_view_holidays'] === 1 ? 'hr-single-grid' : '' }}" aria-label="HR requests and calendar">
        @if ($dashboard['can_view_leaves'])
            <article class="hr-panel">
                <header class="hr-panel-head">
                    <div><h2>Pending leave requests</h2><p>Recent requests waiting for a department decision</p></div>
                </header>
                @if ($dashboard['can_view_employees'])
                    <div class="hr-table-wrap">
                        <table class="hr-table">
                            <thead><tr><th>Employee</th><th>Leave type</th><th>Dates</th><th>Status</th>@if($dashboard['can_review_leaves'])<th>Decision</th>@endif</tr></thead>
                            <tbody>
                            @forelse ($dashboard['pending_leave_requests'] as $leave)
                                <tr>
                                    <td><span class="hr-person-name">{{ trim($leave->first_name . ' ' . $leave->last_name) }}</span></td>
                                    <td>{{ $leave->leave_type }}</td>
                                    <td>{{ \Carbon\Carbon::parse($leave->from_date)->format('d M') }} – {{ \Carbon\Carbon::parse($leave->to_date)->format('d M Y') }}</td>
                                    <td><span class="hr-tag leave">Pending</span></td>
                                    @if($dashboard['can_review_leaves'])
                                        <td>
                                            @if(auth()->user()->isSuperAdmin() || (int) auth()->user()->employee_id !== (int) $leave->employee_id)
                                                <form class="hr-approval-form" method="POST" action="{{ route('portal.leaves.status', $leave->id) }}">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" name="status" value="Approved">Approve</button>
                                                    <button class="reject" type="submit" name="status" value="Rejected">Reject</button>
                                                </form>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr><td colspan="{{ $dashboard['can_review_leaves'] ? 5 : 4 }}" class="hr-empty">There are no pending leave requests.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="hr-restricted">Employee access is required to show names with leave requests.</div>
                @endif
            </article>
        @endif

        @if ($dashboard['can_view_exit_passes'])
            <article class="hr-panel">
                <header class="hr-panel-head">
                    <div><h2>Pending exit passes</h2><p>Requests from employees in your department</p></div>
                    <a class="hr-panel-link" href="{{ route('portal.exit-pass.index') }}">Open exit passes →</a>
                </header>
                @if ($dashboard['can_view_employees'])
                    <div class="hr-table-wrap">
                        <table class="hr-table">
                            <thead><tr><th>Employee</th><th>Exit date</th><th>Reason</th>@if($dashboard['can_review_exit_passes'])<th>Decision</th>@endif</tr></thead>
                            <tbody>
                            @forelse ($dashboard['pending_exit_pass_requests'] as $pass)
                                <tr>
                                    <td><span class="hr-person-name">{{ trim($pass->first_name . ' ' . $pass->last_name) }}</span></td>
                                    <td>{{ \Carbon\Carbon::parse($pass->exit_date)->format('d M Y') }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($pass->reason, 55) }}</td>
                                    @if($dashboard['can_review_exit_passes'])
                                        <td>
                                            @if(auth()->user()->isSuperAdmin() || (int) auth()->user()->employee_id !== (int) $pass->employee_id)
                                                <form class="hr-approval-form" method="POST" action="{{ route('portal.exit-pass.status', $pass->id) }}">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" name="status" value="Approved">Approve</button>
                                                    <button class="reject" type="submit" name="status" value="Rejected">Reject</button>
                                                </form>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr><td colspan="{{ $dashboard['can_review_exit_passes'] ? 4 : 3 }}" class="hr-empty">There are no pending exit pass requests.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="hr-restricted">Employee access is required to identify exit pass requesters.</div>
                @endif
            </article>
        @endif

        @if ($dashboard['can_view_holidays'])
            <article class="hr-panel">
                <header class="hr-panel-head">
                    <div><h2>Upcoming holidays</h2><p>Keep the organization calendar in view</p></div>
                    <a class="hr-panel-link" href="{{ route('portal.holidays.index') }}">Holiday calendar →</a>
                </header>
                <div class="hr-holiday-list">
                    @forelse ($dashboard['upcoming_holidays'] as $holiday)
                        <div class="hr-holiday">
                            <span class="hr-holiday-date"><strong>{{ $holiday->holiday_date->format('d') }}</strong><span>{{ $holiday->holiday_date->format('M') }}</span></span>
                            <span class="hr-holiday-info"><strong>{{ $holiday->name }}</strong><small>{{ $holiday->holiday_date->format('l, d F Y') }}</small></span>
                        </div>
                    @empty
                        <p class="hr-empty">No upcoming holidays are scheduled.</p>
                    @endforelse
                </div>
            </article>
        @endif
    </section>
    @endif
</main>
@endsection
