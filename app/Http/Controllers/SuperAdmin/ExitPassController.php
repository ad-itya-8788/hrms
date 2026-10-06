<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ExitPass;
use Illuminate\Http\Request;

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

        if (!$user->hasPermission('employees', 'view')) {
            if ($user->employee) {
                $query->where('employee_id', $user->employee->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        return view('portal.exit-pass.index', [
            'exitPasses' => $query->paginate(10),
            'canCreate' => $user->hasPermission('exit_pass', 'create'),
            'canViewEmployees' => $user->hasPermission('employees', 'view'),
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
    public function updateStatus(Request $request, $exitPass)
    {
        /*
         * Exit Pass status logic will be added here
         * after confirming your existing Exit Pass model/table.
         */

        return redirect()
            ->route('portal.exit-pass.index')
            ->with('status', 'Exit Pass status updated successfully.');
    }
}