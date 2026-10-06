@extends('layouts.portal')

@section('content')
    @php
        $stats = $dashboard['stats'] ?? [];
        $dateLabel = \Carbon\Carbon::parse($dashboard['date'] ?? now())->format('l, d F Y');
        $canViewEmployees = !empty($dashboard['can_view_employees']);
        $canViewDepartments = !empty($dashboard['can_view_departments']);
    @endphp

    <section class="page-heading">
        <div>
            <p class="eyebrow">{{ strtoupper($dateLabel) }}</p>
            <h1>People dashboard</h1>
            <p class="heading-description">A simple overview of your employees and departments.</p>
        </div>
        <div class="heading-actions">
            @if ($canViewDepartments)
                <a class="button button-secondary" href="{{ route('portal.departments.index') }}">Manage departments</a>
            @endif
            @if ($canViewEmployees)
                <a class="button button-primary" href="{{ route('portal.employees.index') }}">Manage employees</a>
            @endif
        </div>
    </section>

    <section class="stats-grid">
        @if ($canViewEmployees)
            <article class="metric-card">
                <div class="metric-top">
                    <span class="metric-icon metric-violet" aria-hidden="true">♙</span>
                    <span class="metric-mark" aria-hidden="true">↗</span>
                </div>
                <p class="metric-label">Active employees</p>
                <strong class="metric-value">{{ $stats['employees'] }}</strong>
                <p class="metric-note">Current employee records</p>
            </article>
        @endif
        @if ($canViewDepartments)
            <article class="metric-card">
                <div class="metric-top">
                    <span class="metric-icon metric-green" aria-hidden="true">▦</span>
                    <span class="metric-mark" aria-hidden="true">↗</span>
                </div>
                <p class="metric-label">Departments</p>
                <strong class="metric-value">{{ $stats['departments'] }}</strong>
                <p class="metric-note">Teams in your organisation</p>
            </article>
        @endif
    </section>

    @if ($canViewDepartments || $canViewEmployees)
    <div class="dashboard-grid management-grid">
        @if ($canViewDepartments)
        <section class="panel distribution-panel">
            <div class="panel-heading">
                <div><h2>Departments</h2><p>Active employees assigned to each team.</p></div>
                @if ($canViewDepartments)
                    <a class="text-link" href="{{ route('portal.departments.index') }}">Manage →</a>
                @endif
            </div>
            <div class="department-bars">
                @forelse ($dashboard['departments'] as $department)
                    <div class="department-bar-line">
                        <div class="bar-label"><span>{{ $department->name }}</span>@if ($canViewEmployees)<strong>{{ $department->employees_count }}</strong>@endif</div>
                        @if ($canViewEmployees)
                            <div class="bar-rail"><i style="width:{{ $stats['employees'] ? min(100, $department->employees_count / $stats['employees'] * 100) : 0 }}%"></i></div>
                        @endif
                        <small>{{ $department->location }}</small>
                    </div>
                @empty
                    <p class="empty-state">No departments yet.</p>
                @endforelse
            </div>
        </section>
        @endif
        @if ($canViewEmployees)
        <section class="panel table-panel">
            <div class="panel-heading"><div><h2>Recently joined</h2><p>The latest employee records.</p></div><a class="text-link" href="{{ route('portal.employees.index') }}">View employees →</a></div>
            @include('portal.employee_management.table', ['employees' => $dashboard['recent_employees'], 'compact' => true, 'canView' => true])
        </section>
        @endif
    </div>
    @endif
@endsection
