<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LeaveApplication;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isHead = $user->isDepartmentHead();
        abort_unless($user->hasPermission('leaves', 'view') || $isHead, 403);

        $query = LeaveApplication::with('employee.department')
            ->orderByDesc('created_at');
        if ($isHead && !$user->isSuperAdmin()) {
            $query->whereHas('employee', function ($employeeQuery) use ($user) {
                $employeeQuery->whereIn('department_id', $user->headedDepartmentIds());
            });
        } elseif ($user->isEmployeeAccount()) {
            $query->where('employee_id', $user->employee_id ?: 0);
        }

        $leaves = $query->paginate(10);

        return view('portal.leaves.index', [
            'leaves' => $leaves,
            'canCreateLeaves' => $user->hasPermission('leaves', 'create'),
            'canReviewLeaves' => $user->isSuperAdmin()
                || ($user->hasPermission('leaves', 'edit') && !$isHead)
                || $isHead,
            'showEmployee' => !$user->isEmployeeAccount() || $isHead,
        ]);
    }

    public function create()
    {
        return view('portal.leaves.create');
    }

    public function store(Request $request)
    {
        $employee = auth()->user()->employee;

        if (!$employee) {

            return response()->json([
                'success' => false,
                'message' => 'Employee profile not found.'
            ], 404);
        }

        $validated = $request->validate([
            'leave_type' => [
                'required',
                'string',
                'max:100'
            ],

            'from_date' => [
                'required',
                'date'
            ],

            'to_date' => [
                'required',
                'date',
                'after_or_equal:from_date'
            ],

            'reason' => [
                'required',
                'string',
                'max:2000'
            ],
        ]);

        /*
         * Prevent past leave
         */
        if (
            strtotime($validated['from_date']) <
            strtotime(date('Y-m-d'))
        ) {

            return response()->json([
                'success' => false,
                'message' => 'Leave cannot start in the past.',
                'errors' => [
                    'from_date' => [
                        'Leave cannot start in the past.'
                    ]
                ]
            ], 422);
        }

        /*
         * Check overlapping leave
         */
        $overlap = LeaveApplication::where(
            'employee_id',
            $employee->id
        )
        ->whereIn('status', [
            'Pending',
            'Approved'
        ])
        ->where(
            'from_date',
            '<=',
            $validated['to_date']
        )
        ->where(
            'to_date',
            '>=',
            $validated['from_date']
        )
        ->exists();

        if ($overlap) {

            return response()->json([
                'success' => false,
                'message' =>
                    'You already have a pending or approved leave for these dates.',
                'errors' => [
                    'from_date' => [
                        'You already have a pending or approved leave for these dates.'
                    ]
                ]
            ], 422);
        }

        /*
         * Create leave
         */
        $leave = LeaveApplication::create([
            'employee_id' =>
                $employee->id,

            'leave_type' =>
                $validated['leave_type'],

            'from_date' =>
                $validated['from_date'],

            'to_date' =>
                $validated['to_date'],

            'reason' =>
                $validated['reason'],

            'status' =>
                'Pending',
        ]);

        return response()->json([
            'success' => true,
            'message' =>
                'Your leave application has been submitted successfully.',
            'data' => [
                'id' => $leave->id,
                'status' => $leave->status
            ]
        ], 201);
    }

    public function updateStatus(Request $request, LeaveApplication $leave)
    {
        $attributes = $request->validate([
            'status' => 'required|in:Approved,Rejected',
            'admin_remark' => 'nullable|string|max:2000',
        ]);
        $user = $request->user();

        $leave = DB::transaction(function () use ($leave, $attributes, $user) {
            $lockedLeave = LeaveApplication::whereKey($leave->id)->lockForUpdate()->firstOrFail();
            $employee = Employee::findOrFail($lockedLeave->employee_id);
            abort_unless($user->canReviewDepartmentRequest($employee, 'leaves'), 403);
            if (strtolower($lockedLeave->status) !== 'pending') {
                abort(409, 'This leave request has already been reviewed.');
            }

            $lockedLeave->update([
                'status' => $attributes['status'],
                'approved_by' => $user->id,
                'approved_at' => now(),
                'admin_remark' => $attributes['admin_remark'] ?? null,
            ]);

            return $lockedLeave;
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Leave request ' . strtolower($leave->status) . '.',
                'data' => $leave,
            ]);
        }

        return redirect()->back()->with('success', 'Leave request ' . strtolower($leave->status) . '.');
    }
}