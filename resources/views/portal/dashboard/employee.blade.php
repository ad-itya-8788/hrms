@extends('layouts.portal')

@section('content')
    @php
        $employee = $dashboard['employee'] ?? null;
        $canViewProfile = !empty($dashboard['can_view_own_profile']);
        $canViewHolidays = !empty($dashboard['can_view_holidays']);
        $profilePhoto = $employee ? $employee->documents->first(function ($document) {
            return in_array(strtolower(trim($document->title)), ['photo', 'employee photo', 'photograph', 'profile photo'], true)
                && in_array(strtolower($document->mime_type), ['image/jpeg', 'image/png'], true);
        }) : null;
        $dateLabel = \Carbon\Carbon::parse($dashboard['date'] ?? now())->format('l, d F Y');
    @endphp

    <section class="page-heading">
        <div>
            <p class="eyebrow">{{ strtoupper($dateLabel) }}</p>
            <h1>Welcome, {{ data_get($employee, 'first_name') ?: strtok(auth()->user()->name, ' ') }}</h1>
            <p class="heading-description">Your important work details and upcoming holidays.</p>
        </div>
    </section>

    @if ($employee && $canViewProfile)
        <section class="employee-dashboard-profile">
            <div class="employee-dashboard-identity">
                <span class="welcome-avatar">
                    @if ($profilePhoto)
                        <img src="{{ route('portal.employee-documents.preview', $profilePhoto) }}" alt="Profile photo of {{ $employee->full_name }}">
                    @else
                        {{ collect([$employee->first_name, $employee->last_name])->map(function ($part) { return mb_substr($part, 0, 1); })->implode('') }}
                    @endif
                </span>
                <div>
                    <p class="eyebrow">YOUR EMPLOYEE PROFILE</p>
                    <h2>{{ $employee->full_name }}</h2>
                    <p>{{ data_get($employee, 'employeeRole.name', 'Role not assigned') }} · {{ data_get($employee, 'department.name', 'Department not assigned') }}</p>
                </div>
            </div>

            <dl class="employee-dashboard-details">
                <div><dt>Employee ID</dt><dd>{{ $employee->employee_code ?: 'Not assigned' }}</dd></div>
                <div><dt>Work email</dt><dd>{{ $employee->email ?: 'Not provided' }}</dd></div>
                <div><dt>Phone</dt><dd>{{ $employee->phone ?: 'Not provided' }}</dd></div>
                <div><dt>Employment type</dt><dd>{{ data_get($employee, 'employeeType.name', 'Not assigned') }}</dd></div>
                <div><dt>Manager</dt><dd>{{ data_get($employee, 'manager.full_name', 'Not assigned') }}</dd></div>
                <div><dt>Joining date</dt><dd>{{ $employee->joining_date ? $employee->joining_date->format('d M Y') : 'Not recorded' }}</dd></div>
                <div><dt>Employment status</dt><dd>{{ ucwords(str_replace('_', ' ', $employee->employment_status ?: 'Not recorded')) }}</dd></div>
            </dl>

            <a class="text-link" href="{{ route('portal.employees.show', $employee->id) }}">View complete profile and documents</a>
        </section>
    @elseif (!$canViewProfile)
        <div class="empty-state">
            <strong>Profile details are not available</strong>
            <p>Your account does not currently have access to the employee profile module.</p>
        </div>
    @else
        <div class="empty-state">
            <span class="empty-mark" aria-hidden="true">▧</span>
            <strong>No employee profile linked</strong>
            <p>Ask an administrator to link your account to an employee record.</p>
        </div>
    @endif

    @if ($canViewHolidays)
        <section class="employee-dashboard-holidays panel">
            <div class="panel-heading">
                <div>
                    <h2>Upcoming holidays</h2>
                    <p>Next scheduled holidays for your organization.</p>
                </div>
                <a class="text-link" href="{{ route('portal.holidays.index') }}">Open holiday calendar</a>
            </div>
            @forelse ($dashboard['upcoming_holidays'] as $holiday)
                <article class="employee-upcoming-holiday">
                    <div class="holiday-date-badge">
                        <strong>{{ $holiday->holiday_date->format('d') }}</strong>
                        <span>{{ $holiday->holiday_date->format('M') }}</span>
                    </div>
                    <div>
                        <h3>{{ $holiday->name }}</h3>
                        <p>{{ $holiday->holiday_date->format('l, d F Y') }} · {{ ucwords(str_replace('_', ' ', $holiday->holiday_type)) }}</p>
                        @if ($holiday->description)
                            <p>{{ $holiday->description }}</p>
                        @endif
                    </div>
                </article>
            @empty
                <p class="empty-state">There are no upcoming holidays scheduled.</p>
            @endforelse
        </section>
    @endif

    <style>
        .employee-dashboard-profile, .employee-dashboard-holidays { margin-bottom: 18px; padding: 22px; border: 1px solid #e5e7eb; border-radius: 14px; background: #fff; }
        .employee-dashboard-identity { display: flex; align-items: center; gap: 16px; padding-bottom: 20px; border-bottom: 1px solid #eef0ee; }
        .employee-dashboard-identity .welcome-avatar { display: grid; flex: 0 0 68px; width: 68px; height: 68px; overflow: hidden; place-items: center; border-radius: 18px; font-size: 22px; }
        .employee-dashboard-identity .welcome-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .employee-dashboard-identity h2 { margin: 0; }
        .employee-dashboard-identity p:last-child { margin: 5px 0 0; color: #737d76; }
        .employee-dashboard-details { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin: 0; padding: 20px 0; }
        .employee-dashboard-details div { min-width: 0; }
        .employee-dashboard-details dt { margin-bottom: 4px; color: #737d76; font-size: 12px; font-weight: 700; }
        .employee-dashboard-details dd { margin: 0; overflow-wrap: anywhere; font-size: 14px; font-weight: 650; }
        .employee-dashboard-holidays .panel-heading { padding: 0 0 12px; }
        .employee-upcoming-holiday { display: flex; align-items: center; gap: 14px; padding: 14px 0; border-top: 1px solid #eef0ee; }
        .employee-upcoming-holiday h3 { margin: 0; font-size: 15px; }
        .employee-upcoming-holiday p { margin: 4px 0 0; color: #737d76; font-size: 13px; }
        @media (max-width: 560px) {
            .employee-dashboard-profile, .employee-dashboard-holidays { padding: 16px; }
            .employee-dashboard-identity { align-items: flex-start; }
        }
    </style>
@endsection
