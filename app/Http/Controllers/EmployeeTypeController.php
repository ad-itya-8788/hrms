<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EmployeeType;
use Illuminate\Http\Request;

class EmployeeTypeController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['status' => 'nullable|in:active,inactive']);
        $status = $request->input('status', 'active');
        $canViewEmployees = $request->user()->hasPermission('employees', 'view');
        $employeeTypes = EmployeeType::query();
        if ($canViewEmployees) {
            $employeeTypes->withCount('employees');
        }

        return view('portal.employee_types.index', [
            'employeeTypes' => $employeeTypes
                ->where('is_active', $status === 'active')
                ->orderBy('name')
                ->get(),
            'canViewEmployees' => $canViewEmployees,
            'activeTypeCount' => EmployeeType::where('is_active', true)->count(),
            'inactiveTypeCount' => EmployeeType::where('is_active', false)->count(),
            'selectedStatus' => $status,
            'pageTitle' => 'Employee types',
        ]);
    }

    public function store(Request $request)
    {
        $attributes = $request->validate([
            'name' => 'required|string|max:80|unique:employee_types,name',
            'description' => 'nullable|string|max:255',
        ]);
        $employeeType = EmployeeType::create($attributes);
        if ($request->user()->hasPermission('employees', 'view')) {
            $employeeType->loadCount('employees');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $employeeType,
                'message' => 'Employee type created.',
            ], 201);
        }

        return redirect()->route('portal.employee-types.index')->with('status', 'Employee type created.');
    }

    public function update(Request $request, EmployeeType $employeeType)
    {
        $employeeType->update($request->validate([
            'name' => 'required|string|max:80|unique:employee_types,name,' . $employeeType->id,
            'description' => 'nullable|string|max:255',
        ]));
        if ($request->user()->hasPermission('employees', 'view')) {
            $employeeType->loadCount('employees');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $employeeType,
                'message' => 'Employee type updated.',
            ]);
        }

        return redirect()->route('portal.employee-types.index')->with('status', 'Employee type updated.');
    }

    public function updateStatus(Request $request, EmployeeType $employeeType)
    {
        $attributes = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $employeeType->update($attributes);
        if ($request->user()->hasPermission('employees', 'view')) {
            $employeeType->loadCount('employees');
        }

        return response()->json([
            'data' => $employeeType,
            'message' => $employeeType->is_active ? 'Employee type activated.' : 'Employee type deactivated.',
        ]);
    }
}
