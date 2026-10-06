@extends('layouts.portal')

@section('title', 'My Leave Applications')

@section('content')

<style>
    .leave-page {
        max-width: 1200px;
        margin: 0 auto;
        padding: 24px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        margin-bottom: 24px;
    }

    .eyebrow {
        margin: 0 0 6px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .08em;
        color: #6b7280;
        text-transform: uppercase;
    }

    .page-header h1 {
        margin: 0;
        font-size: 28px;
        font-weight: 800;
        color: #111827;
    }

    .page-header p {
        margin: 7px 0 0;
        color: #6b7280;
        font-size: 14px;
    }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        padding: 10px 17px;
        background: #111827;
        color: #ffffff;
        border: 1px solid #111827;
        border-radius: 8px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 700;
    }

    .btn-primary:hover {
        background: #1f2937;
        color: #ffffff;
    }

    .alert {
        padding: 13px 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 13px;
    }

    .alert-success {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
    }

    .leave-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(15, 23, 42, .05);
    }

    .card-header {
        padding: 18px 20px;
        border-bottom: 1px solid #e5e7eb;
    }

    .card-header h2 {
        margin: 0;
        font-size: 17px;
        font-weight: 750;
        color: #111827;
    }

    .card-header p {
        margin: 5px 0 0;
        font-size: 13px;
        color: #6b7280;
    }

    .table-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .leave-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 760px;
    }

    .leave-table th {
        padding: 13px 16px;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
        color: #6b7280;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        text-align: left;
        white-space: nowrap;
    }

    .leave-table td {
        padding: 15px 16px;
        border-bottom: 1px solid #f1f5f9;
        color: #374151;
        font-size: 13px;
        vertical-align: top;
    }

    .leave-table tbody tr:hover {
        background: #fafafa;
    }

    .leave-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .leave-type {
        font-weight: 700;
        color: #111827;
    }

    .reason {
        max-width: 280px;
        line-height: 1.5;
        color: #4b5563;
    }

    .date-text {
        white-space: nowrap;
        color: #374151;
    }

    .status {
        display: inline-flex;
        align-items: center;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .status-pending {
        background: #fff7ed;
        color: #c2410c;
    }

    .status-approved {
        background: #f0fdf4;
        color: #15803d;
    }

    .status-rejected {
        background: #fef2f2;
        color: #b91c1c;
    }

    .status-cancelled {
        background: #f3f4f6;
        color: #4b5563;
    }

    .leave-actions { display: flex; gap: 6px; }
    .leave-actions button { padding: 6px 9px; border: 1px solid #b9e5c7; border-radius: 6px; background: #f0fdf4; color: #166534; font-size: 11px; font-weight: 700; font-family: inherit; cursor: pointer; }
    .leave-actions button.reject { border-color: #f2c6c2; background: #fff5f4; color: #a12b22; }

    .empty-state {
        padding: 55px 20px;
        text-align: center;
    }

    .empty-state h3 {
        margin: 0 0 7px;
        font-size: 17px;
        color: #111827;
    }

    .empty-state p {
        margin: 0 0 18px;
        font-size: 13px;
        color: #6b7280;
    }

    .pagination-wrapper {
        padding: 16px 20px;
        border-top: 1px solid #e5e7eb;
    }

    @media (max-width: 700px) {
        .leave-page {
            padding: 16px;
        }

        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .btn-primary {
            width: 100%;
        }

        .page-header h1 {
            font-size: 24px;
        }
    }
</style>

<div class="leave-page">

    <div class="page-header">

        <div>
            <p class="eyebrow">{{ $showEmployee ? 'DEPARTMENT · LEAVE MANAGEMENT' : 'EMPLOYEE · LEAVE MANAGEMENT' }}</p>

            <h1>{{ $showEmployee ? 'Department leave requests' : 'My Leave Applications' }}</h1>

            <p>
                {{ $showEmployee ? 'Review leave requests submitted by employees in your department.' : 'View and track all your submitted leave applications.' }}
            </p>
        </div>

        @if ($canCreateLeaves)
            <a href="{{ route('portal.leaves.create') }}" class="btn-primary">+ Apply for Leave</a>
        @endif

    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="leave-card">

        <div class="card-header">

            <h2>Leave History</h2>

            <p>
                {{ $showEmployee ? 'Requests from employees in your department.' : 'Your recent leave applications and their current status.' }}
            </p>

        </div>

        @if ($leaves->count())

            <div class="table-wrapper">

                <table class="leave-table">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Leave Type</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Applied On</th>
                            @if ($showEmployee)<th>Employee</th>@endif
                            @if ($canReviewLeaves)<th>Decision</th>@endif
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($leaves as $leave)

                            <tr>

                                <td>
                                    {{ $leaves->firstItem() + $loop->index }}
                                </td>

                                <td>
                                    <div class="leave-type">
                                        {{ $leave->leave_type }}
                                    </div>
                                </td>

                                <td>
                                    <span class="date-text">
                                        {{ $leave->from_date->format('d M Y') }}
                                    </span>
                                </td>

                                <td>
                                    <span class="date-text">
                                        {{ $leave->to_date->format('d M Y') }}
                                    </span>
                                </td>

                                <td>
                                    <div class="reason">
                                        {{ $leave->reason }}
                                    </div>
                                </td>

                                <td>

                                    @if ($leave->status == 'Approved')

                                        <span class="status status-approved">
                                            Approved
                                        </span>

                                    @elseif ($leave->status == 'Rejected')

                                        <span class="status status-rejected">
                                            Rejected
                                        </span>

                                    @elseif ($leave->status == 'Cancelled')

                                        <span class="status status-cancelled">
                                            Cancelled
                                        </span>

                                    @else

                                        <span class="status status-pending">
                                            Pending
                                        </span>

                                    @endif

                                </td>

                                <td>
                                    <span class="date-text">
                                        {{ $leave->created_at->format('d M Y') }}
                                    </span>
                                </td>
                                @if ($showEmployee)
                                    <td>{{ optional($leave->employee)->full_name ?: 'Employee record unavailable' }}</td>
                                @endif
                                @if ($canReviewLeaves)
                                    <td>
                                        @if ($leave->status === 'Pending' && (auth()->user()->isSuperAdmin() || (int) auth()->user()->employee_id !== (int) $leave->employee_id))
                                            <form class="leave-actions" method="POST" action="{{ route('portal.leaves.status', $leave->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" name="status" value="Approved">Approve</button>
                                                <button class="reject" type="submit" name="status" value="Rejected">Reject</button>
                                            </form>
                                        @else
                                            —
                                        @endif
                                    </td>
                                @endif

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

            @if ($leaves->hasPages())

                <div class="pagination-wrapper">
                    {{ $leaves->links() }}
                </div>

            @endif

        @else

            <div class="empty-state">

                <h3>No leave applications yet</h3>

                <p>
                    You haven't submitted any leave application.
                </p>

                @if ($canCreateLeaves)
                    <a href="{{ route('portal.leaves.create') }}" class="btn-primary">Apply for Leave</a>
                @endif

            </div>

        @endif

    </div>

</div>

@endsection