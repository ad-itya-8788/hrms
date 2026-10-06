<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Holiday;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function page(Request $request)
    {
        $user = $request->user();
        abort_unless($user->isModuleEnabled('dashboard'), 403);
        $employeeRole = $user->role === 'employee';
        $canViewEmployees = $user->hasPermission('employees', 'view');
        $canViewDepartments = $user->hasPermission('departments', 'view');
        $canViewOwnProfile = $employeeRole && $user->hasPermission('employee_profile', 'view');
        $canViewHolidays = $user->hasPermission('holidays', 'view');
        $employee = $canViewOwnProfile
            ? $user->employee()->with(['department', 'employeeType', 'employeeRole', 'manager', 'documents'])->first()
            : null;
        $departmentsQuery = Department::where('is_active', true);
        if ($canViewEmployees) {
            $departmentsQuery->withCount(['employees' => function ($query) {
                $query->where('employment_status', '!=', 'inactive');
            }]);
        }
        $dashboard = [
            'date' => now(),
            'employee' => $employee,
            'can_view_own_profile' => $canViewOwnProfile,
            'can_view_holidays' => $canViewHolidays,
            'upcoming_holidays' => $employeeRole && $canViewHolidays
                ? Holiday::whereDate('holiday_date', '>=', now()->toDateString())
                    ->orderBy('holiday_date')
                    ->orderBy('name')
                    ->limit(6)
                    ->get()
                : collect(),
            'can_view_employees' => $canViewEmployees,
            'can_view_departments' => $canViewDepartments,
            'stats' => $employeeRole ? [] : [
                'employees' => $canViewEmployees
                    ? Employee::where('employment_status', '!=', 'inactive')->count()
                    : null,
                'departments' => $canViewDepartments
                    ? Department::where('is_active', true)->count()
                    : null,
            ],
            'departments' => !$employeeRole && $canViewDepartments
                ? $departmentsQuery->orderBy('name')->get()
                : collect(),
            'recent_employees' => !$employeeRole && $canViewEmployees
                ? Employee::with(['department', 'employeeType', 'employeeRole'])
                ->where('employment_status', '!=', 'inactive')
                ->orderByDesc('joining_date')
                ->limit(5)
                ->get()
                : collect(),
        ];

        if ($employeeRole) {
            $view = 'portal.dashboard.employee';
        } else {
            $view = 'portal.dashboard.index';
        }

        $pageTitle = $employeeRole
            ? 'My dashboard'
            : ($user->isSuperAdmin() ? 'Super Admin dashboard' : 'People dashboard');

        return view($view, [
            'dashboard' => $dashboard,
            'pageTitle' => $pageTitle,
        ]);
    }

}
