<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function page(Request $request)
    {
        $user = $request->user();
        abort_unless($user->isModuleEnabled('dashboard'), 403);
        if ($user->isSuperAdmin()) {
            return view('portal.dashboard.super-admin', [
                'pageTitle' => 'Super Admin dashboard',
                'statisticsUrl' => route('portal.dashboard.statistics'),
                'date' => now(),
            ]);
        }

        $employeeRole = $user->isEmployeeAccount();
        $isDepartmentHead = $user->isDepartmentHead();
        $departmentIds = $user->headedDepartmentIds();
        $canViewOwnProfile = $employeeRole && $user->hasPermission('employee_profile', 'view');
        $employee = ($canViewOwnProfile || $isDepartmentHead)
            ? $user->employee()->with(['department', 'employeeType', 'employeeRole', 'manager', 'documents'])->first()
            : null;
        $canViewEmployees = $user->hasPermission('employees', 'view') || $isDepartmentHead;
        $canViewDepartments = $user->hasPermission('departments', 'view');
        $canViewLeaves = $user->hasPermission('leaves', 'view') || $isDepartmentHead;
        $canViewExitPasses = $user->hasPermission('exit_pass', 'view') || $isDepartmentHead;
        $canViewHolidays = $user->hasPermission('holidays', 'view');
        if ($user->role === 'hr' || $isDepartmentHead) {
            $employeeQuery = function () use ($isDepartmentHead, $departmentIds, $user) {
                $query = Employee::query();
                if ($isDepartmentHead && !$user->isSuperAdmin()) {
                    $query->whereIn('department_id', $departmentIds);
                }
                return $query;
            };
            $departmentQuery = Department::where('is_active', true);
            if ($isDepartmentHead && !$user->isSuperAdmin()) {
                $departmentQuery->whereIn('id', $departmentIds);
            }
            $hrDashboard = [
                'date' => now(),
                'total_employees' => $canViewEmployees
                    ? $employeeQuery()->count()
                    : null,
                'active_employees' => $canViewEmployees
                    ? $employeeQuery()->where('employment_status', 'active')->count()
                    : null,
                'on_leave_employees' => $canViewEmployees
                    ? $employeeQuery()->where('employment_status', 'on_leave')->count()
                    : null,
                'new_hires_this_month' => $canViewEmployees
                    ? $employeeQuery()->whereBetween('joining_date', [now()->startOfMonth()->toDateString(), now()->toDateString()])->count()
                    : null,
                'pending_leaves' => $canViewLeaves
                    ? DB::table('leave_applications')
                        ->join('employees', 'employees.id', '=', 'leave_applications.employee_id')
                        ->when($isDepartmentHead && !$user->isSuperAdmin(), function ($query) use ($departmentIds) {
                            $query->whereIn('employees.department_id', $departmentIds);
                        })
                        ->whereRaw('LOWER(leave_applications.status) = ?', ['pending'])
                        ->count()
                    : null,
                'pending_exit_passes' => $canViewExitPasses
                    ? DB::table('exit_passes')
                        ->join('employees', 'employees.id', '=', 'exit_passes.employee_id')
                        ->when($isDepartmentHead && !$user->isSuperAdmin(), function ($query) use ($departmentIds) {
                            $query->whereIn('employees.department_id', $departmentIds);
                        })
                        ->whereRaw('LOWER(exit_passes.status) = ?', ['pending'])
                        ->count()
                    : null,
                'can_view_employees' => $canViewEmployees,
                'can_create_employees' => $user->hasPermission('employees', 'create'),
                'can_view_departments' => $canViewDepartments,
                'can_view_leaves' => $canViewLeaves,
                'can_review_leaves' => $canViewLeaves && ($isDepartmentHead || $user->hasPermission('leaves', 'edit')),
                'can_view_exit_passes' => $canViewExitPasses,
                'can_review_exit_passes' => $canViewExitPasses && ($isDepartmentHead || $user->hasPermission('exit_pass', 'edit')),
                'can_view_holidays' => $canViewHolidays,
                'is_department_head_employee' => $employeeRole && $isDepartmentHead,
                'employee_profile' => $employee,
                'departments' => $canViewDepartments
                    ? $departmentQuery
                        ->when($canViewEmployees, function ($query) {
                            $query->withCount(['employees' => function ($employeeQuery) {
                                $employeeQuery->where('employment_status', '!=', 'inactive');
                            }]);
                        })
                        ->orderBy('name')
                        ->limit(6)
                        ->get()
                    : collect(),
                'recent_employees' => $canViewEmployees
                    ? $employeeQuery()->with('department')
                        ->where('employment_status', '!=', 'inactive')
                        ->orderByDesc('joining_date')
                        ->limit(100)
                        ->get()
                    : collect(),
                'pending_leave_requests' => $canViewLeaves && $canViewEmployees
                    ? DB::table('leave_applications')
                        ->join('employees', 'employees.id', '=', 'leave_applications.employee_id')
                        ->when($isDepartmentHead && !$user->isSuperAdmin(), function ($query) use ($departmentIds) {
                            $query->whereIn('employees.department_id', $departmentIds);
                        })
                        ->whereRaw('LOWER(leave_applications.status) = ?', ['pending'])
                        ->orderByDesc('leave_applications.created_at')
                        ->limit(5)
                        ->get([
                            'leave_applications.id',
                            'leave_applications.employee_id',
                            'leave_applications.leave_type',
                            'leave_applications.from_date',
                            'leave_applications.to_date',
                            'employees.first_name',
                            'employees.last_name',
                        ])
                    : collect(),
                'pending_exit_pass_requests' => $canViewExitPasses && $canViewEmployees
                    ? DB::table('exit_passes')
                        ->join('employees', 'employees.id', '=', 'exit_passes.employee_id')
                        ->when($isDepartmentHead && !$user->isSuperAdmin(), function ($query) use ($departmentIds) {
                            $query->whereIn('employees.department_id', $departmentIds);
                        })
                        ->whereRaw('LOWER(exit_passes.status) = ?', ['pending'])
                        ->orderByDesc('exit_passes.created_at')
                        ->limit(5)
                        ->get([
                            'exit_passes.id',
                            'exit_passes.employee_id',
                            'exit_passes.exit_date',
                            'exit_passes.exit_time',
                            'exit_passes.reason',
                            'employees.first_name',
                            'employees.last_name',
                        ])
                    : collect(),
                'upcoming_holidays' => $canViewHolidays
                    ? Holiday::whereDate('holiday_date', '>=', now()->toDateString())
                        ->orderBy('holiday_date')
                        ->orderBy('name')
                        ->limit(5)
                        ->get()
                    : collect(),
            ];

            return view('portal.dashboard.hr', [
                'dashboard' => $hrDashboard,
                'pageTitle' => 'HR dashboard',
            ]);
        }

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

    public function statistics()
    {
        $employeeStatuses = DB::table('employees')
            ->select('employment_status as label', DB::raw('COUNT(*) as total'))
            ->groupBy('employment_status')
            ->orderBy('employment_status')
            ->get()
            ->map(function ($row) {
                return ['label' => ucwords(str_replace('_', ' ', $row->label)), 'value' => (int) $row->total];
            })->values();

        $departmentCounts = DB::table('departments')
            ->leftJoin('employees', 'employees.department_id', '=', 'departments.id')
            ->select('departments.name as label', DB::raw('COUNT(employees.id) as total'))
            ->groupBy('departments.id', 'departments.name')
            ->orderByDesc('total')
            ->orderBy('departments.name')
            ->limit(8)
            ->get()
            ->map(function ($row) {
                return ['label' => $row->label, 'value' => (int) $row->total];
            })->values();

        $leaveStatuses = DB::table('leave_applications')
            ->select('status as label', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->orderBy('status')
            ->get()
            ->map(function ($row) {
                return ['label' => $row->label, 'value' => (int) $row->total];
            })->values();

        $exitPassStatuses = DB::table('exit_passes')
            ->select('status as label', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->orderBy('status')
            ->get()
            ->map(function ($row) {
                return ['label' => $row->label, 'value' => (int) $row->total];
            })->values();

        $userRoles = DB::table('users')
            ->join('user_roles', 'user_roles.id', '=', 'users.role_id')
            ->select('user_roles.name as label', DB::raw('COUNT(users.id) as total'))
            ->groupBy('user_roles.id', 'user_roles.name')
            ->orderByDesc('total')
            ->orderBy('user_roles.name')
            ->get()
            ->map(function ($row) {
                return ['label' => ucwords(str_replace('_', ' ', $row->label)), 'value' => (int) $row->total];
            })->values();

        $monthStart = now()->startOfMonth()->subMonths(5);
        $monthRows = DB::table('employees')
            ->select(DB::raw('YEAR(joining_date) as year'), DB::raw('MONTH(joining_date) as month'), DB::raw('COUNT(*) as total'))
            ->whereBetween('joining_date', [$monthStart->toDateString(), now()->toDateString()])
            ->groupBy(DB::raw('YEAR(joining_date)'), DB::raw('MONTH(joining_date)'))
            ->get()
            ->keyBy(function ($row) {
                return sprintf('%04d-%02d', $row->year, $row->month);
            });
        $monthlyJoiners = [];
        for ($offset = 0; $offset < 6; $offset++) {
            $month = $monthStart->copy()->addMonths($offset);
            $key = $month->format('Y-m');
            $monthlyJoiners[] = [
                'label' => $month->format('M'),
                'value' => isset($monthRows[$key]) ? (int) $monthRows[$key]->total : 0,
            ];
        }

        $totalEmployees = DB::table('employees')->count();
        $activeEmployees = DB::table('employees')->where('employment_status', 'active')->count();
        $onLeaveEmployees = DB::table('employees')->where('employment_status', 'on_leave')->count();
        $pendingLeaveApplications = DB::table('leave_applications')
            ->whereRaw('LOWER(status) = ?', ['pending'])
            ->count();
        $recentLeaveRequests = DB::table('leave_applications')
            ->join('employees', 'employees.id', '=', 'leave_applications.employee_id')
            ->select(
                'leave_applications.id',
                'leave_applications.leave_type',
                'leave_applications.from_date',
                'leave_applications.to_date',
                'leave_applications.status',
                'employees.first_name',
                'employees.last_name'
            )
            ->orderByDesc('leave_applications.created_at')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                return [
                    'id' => (int) $row->id,
                    'employee' => trim($row->first_name . ' ' . $row->last_name),
                    'leave_type' => $row->leave_type,
                    'from_date' => $row->from_date,
                    'to_date' => $row->to_date,
                    'status' => $row->status,
                ];
            })->values();
        $upcomingHolidays = DB::table('holidays')
            ->whereDate('holiday_date', '>=', now()->toDateString())
            ->orderBy('holiday_date')
            ->orderBy('name')
            ->limit(5)
            ->get(['id', 'name', 'holiday_date'])
            ->map(function ($holiday) {
                return [
                    'id' => (int) $holiday->id,
                    'name' => $holiday->name,
                    'date' => $holiday->holiday_date,
                ];
            })->values();

        return response()->json([
            'success' => true,
            'generated_at' => now()->toIso8601String(),
            'totals' => [
                'users' => DB::table('users')->count(),
                'active_users' => DB::table('users')->where('is_active', true)->count(),
                'inactive_users' => DB::table('users')->where('is_active', false)->count(),
                'employees' => $totalEmployees,
                'active_employees' => $activeEmployees,
                'on_leave_employees' => $onLeaveEmployees,
                'pending_leave_applications' => $pendingLeaveApplications,
                'inactive_employees' => DB::table('employees')->where('employment_status', 'inactive')->count(),
                'departments' => DB::table('departments')->count(),
                'active_departments' => DB::table('departments')->where('is_active', true)->count(),
                'inactive_departments' => DB::table('departments')->where('is_active', false)->count(),
                'employee_types' => DB::table('employee_types')->count(),
                'employee_roles' => DB::table('employee_roles')->count(),
                'user_roles' => DB::table('user_roles')->count(),
                'documents' => DB::table('employee_documents')->count(),
                'educations' => DB::table('employee_educations')->count(),
                'experiences' => DB::table('employee_experiences')->count(),
                'bank_details' => DB::table('employee_bank_details')->count(),
                'leave_applications' => DB::table('leave_applications')->count(),
                'exit_passes' => DB::table('exit_passes')->count(),
                'holidays' => DB::table('holidays')->count(),
                'modules' => DB::table('module_access')->distinct()->count('module_name'),
                'active_rate' => $totalEmployees
                    ? (int) round($activeEmployees * 100 / $totalEmployees)
                    : 0,
            ],
            'charts' => [
                'employee_statuses' => $employeeStatuses,
                'department_headcount' => $departmentCounts,
                'leave_statuses' => $leaveStatuses,
                'exit_pass_statuses' => $exitPassStatuses,
                'user_roles' => $userRoles,
                'monthly_joiners' => $monthlyJoiners,
            ],
            'recent_leave_requests' => $recentLeaveRequests,
            'upcoming_holidays' => $upcomingHolidays,
        ]);
    }

}
