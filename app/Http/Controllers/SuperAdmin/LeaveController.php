<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\LeaveApplication;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function index()
    {
        $employee = auth()->user()->employee;

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee profile not found.'
            ], 404);
        }

        $leaves = LeaveApplication::where(
            'employee_id',
            $employee->id
        )
        ->orderBy('created_at', 'desc')
        ->paginate(10);

        return view('portal.leaves.index', [
            'leaves' => $leaves
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
}