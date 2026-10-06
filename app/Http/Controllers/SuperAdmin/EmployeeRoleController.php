<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeRole;
use Illuminate\Http\Request;

class EmployeeRoleController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['status' => 'nullable|in:active,inactive']);
        $status = $request->input('status', 'active');
        $canViewEmployees = $request->user()->hasPermission('employees', 'view');
        $employeeRoles = EmployeeRole::query();
        if ($canViewEmployees) {
            $employeeRoles->withCount('employees');
        }

        return view('portal.employee_roles.index', [
            'employeeRoles' => $employeeRoles
                ->where('is_active', $status === 'active')
                ->orderBy('name')
                ->get(),
            'canViewEmployees' => $canViewEmployees,
            'activeRoleCount' => EmployeeRole::where('is_active', true)->count(),
            'inactiveRoleCount' => EmployeeRole::where('is_active', false)->count(),
            'selectedStatus' => $status,
            'pageTitle' => 'Employee roles',
        ]);
    }

    public function store(Request $request)
    {
        $attributes = $request->validate([
            'name' => 'required|string|max:120|unique:employee_roles,name',
            'description' => 'nullable|string|max:255',
        ]);
        $role = EmployeeRole::create($attributes);
        if ($request->user()->hasPermission('employees', 'view')) {
            $role->loadCount('employees');
        }

        if ($request->expectsJson()) {
            return response()->json(['data' => $role, 'message' => 'Employee role created.'], 201);
        }

        return redirect()->route('portal.employee-roles.index')->with('status', 'Employee role created.');
    }

    public function update(Request $request, EmployeeRole $employeeRole)
    {
        $employeeRole->update($request->validate([
            'name' => 'required|string|max:120|unique:employee_roles,name,' . $employeeRole->id,
            'description' => 'nullable|string|max:255',
        ]));
        if ($request->user()->hasPermission('employees', 'view')) {
            $employeeRole->loadCount('employees');
        }

        if ($request->expectsJson()) {
            return response()->json(['data' => $employeeRole, 'message' => 'Employee role updated.']);
        }

        return redirect()->route('portal.employee-roles.index')->with('status', 'Employee role updated.');
    }

    public function updateStatus(Request $request, EmployeeRole $employeeRole)
    {
        $attributes = $request->validate(['is_active' => 'required|boolean']);
        $employeeRole->update($attributes);
        if ($request->user()->hasPermission('employees', 'view')) {
            $employeeRole->loadCount('employees');
        }

        return response()->json([
            'data' => $employeeRole,
            'message' => $employeeRole->is_active ? 'Employee role activated.' : 'Employee role deactivated.',
        ]);
    }
}
