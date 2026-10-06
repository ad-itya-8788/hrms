<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ExitPass;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExitPassController extends Controller
{
    /**
     * Display Exit Pass records.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = ExitPass::with(['employee.department', 'employee.documents'])
            ->orderByDesc('exit_date')
            ->orderByDesc('exit_time');

        $isHead = $user->isDepartmentHead();
        $canViewRequests = $user->hasPermission('exit_pass', 'view') || $isHead;
        abort_unless($canViewRequests, 403);
        if ($isHead && !$user->isSuperAdmin()) {
            $query->whereHas('employee', function ($employeeQuery) use ($user) {
                $employeeQuery->whereIn('department_id', $user->headedDepartmentIds());
            });
        } elseif ($user->isEmployeeAccount()) {
            $query->where('employee_id', $user->employee_id ?: 0);
        }

        return view('portal.exit-pass.index', [
            'exitPasses' => $query->paginate(10),
            'canCreate' => $user->hasPermission('exit_pass', 'create'),
            'canViewEmployees' => $user->canViewEmployeeDirectory(),
            'canReviewExitPasses' => $user->isSuperAdmin() || $isHead,
            'pageTitle' => 'Exit Passes',
        ]);
    }

    /**
     * Show the Exit Pass creation form.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('portal.exit-pass.create');
    }

    /**
     * Store a new Exit Pass.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $employee = $request->user()->employee;

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Your employee profile could not be found.',
            ], 404);
        }

        $validated = $request->validate([
            'exit_date' => 'required|date|after_or_equal:today',
            'exit_time' => 'required|date_format:H:i',
            'expected_return_time' => 'nullable|date_format:H:i|after:exit_time',
            'destination' => 'nullable|string|max:255',
            'reason' => 'required|string|max:500',
            'remarks' => 'nullable|string|max:5000',
        ]);

        $exitPass = ExitPass::create([
            'employee_id' => $employee->id,
            'exit_date' => $validated['exit_date'],
            'exit_time' => $validated['exit_time'],
            'expected_return_time' => $validated['expected_return_time'] ?? null,
            'destination' => $validated['destination'] ?? null,
            'reason' => $validated['reason'],
            'remarks' => $validated['remarks'] ?? null,
            'status' => 'Pending',
        ]);

        if (!$request->expectsJson()) {
            return redirect()
                ->route('portal.exit-pass.index')
                ->with('status', 'Exit pass submitted successfully.');
        }

        return response()->json([
            'success' => true,
            'message' => 'Your exit pass request has been submitted successfully.',
            'data' => [
                'id' => $exitPass->id,
                'status' => $exitPass->status,
            ],
        ], 201);
    }

    /**
     * Update an Exit Pass.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $exitPass
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $exitPass)
    {
        /*
         * Exit Pass update logic will be added here
         * after confirming your existing Exit Pass model/table.
         */

        return redirect()
            ->route('portal.exit-pass.index')
            ->with('status', 'Exit Pass updated successfully.');
    }

    /**
     * Update Exit Pass status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $exitPass
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, ExitPass $exitPass)
    {
        $attributes = $request->validate([
            'status' => 'required|in:Approved,Rejected',
            'admin_remark' => 'nullable|string|max:2000',
        ]);
        $user = $request->user();
        $exitPass = DB::transaction(function () use ($exitPass, $attributes, $user) {
            $lockedPass = ExitPass::whereKey($exitPass->id)->lockForUpdate()->firstOrFail();
            $employee = Employee::findOrFail($lockedPass->employee_id);
            abort_unless($user->canReviewDepartmentRequest($employee, 'exit_pass'), 403);
            if (strtolower($lockedPass->status) !== 'pending') {
                abort(409, 'This exit pass has already been reviewed.');
            }

            $lockedPass->update([
                'status' => $attributes['status'],
                'approved_by' => $user->id,
                'approved_at' => now(),
                'admin_remark' => $attributes['admin_remark'] ?? null,
            ]);

            return $lockedPass;
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Exit pass ' . strtolower($exitPass->status) . '.',
                'data' => $exitPass,
            ]);
        }

        return redirect()->back()->with('status', 'Exit pass ' . strtolower($exitPass->status) . '.');
    }
}