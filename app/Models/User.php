<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use App\Models\Department;
use App\Models\Employee;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'is_active', 'employee_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function createdEmployees()
    {
        return $this->hasMany(Employee::class, 'created_by');
    }

    public function userRole()
    {
        return $this->belongsTo(UserRole::class, 'role_id');
    }

    public function getRoleAttribute($value)
    {
        return $this->userRole ? $this->userRole->name : $value;
    }

    public function isSuperAdmin()
    {
        return in_array($this->role, ['superadmin', 'super_admin', 'admin'], true);
    }

    public function isEmployeeAccount()
    {
        return in_array($this->role, ['emp', 'employee'], true);
    }

    public function headedDepartmentIds()
    {
        if (!$this->employee_id) {
            return collect();
        }

        return Department::where('head_employee_id', $this->employee_id)->pluck('id');
    }

    public function isDepartmentHead()
    {
        return $this->headedDepartmentIds()->isNotEmpty();
    }

    public function isDepartmentHeadOfEmployee(Employee $employee)
    {
        return $this->employee_id
            && Department::where('id', $employee->department_id)
                ->where('head_employee_id', $this->employee_id)
                ->exists();
    }

    public function canViewEmployeeRecord(Employee $employee)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->isDepartmentHead()) {
            return $this->isDepartmentHeadOfEmployee($employee);
        }

        return $this->hasPermission('employees', 'view');
    }

    public function canViewEmployeeDirectory()
    {
        return $this->hasPermission('employees', 'view') || $this->isDepartmentHead();
    }

    public function canReviewDepartmentRequest(Employee $employee, $module)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->isDepartmentHead()) {
            return (int) $this->employee_id !== (int) $employee->id
                && $this->isDepartmentHeadOfEmployee($employee);
        }

        return (int) $this->employee_id !== (int) $employee->id
            && $this->hasPermission($module, 'edit');
    }

    public function hasPermission($module, $action = 'view')
    {
        return $this->isSuperAdmin() || $this->isModuleEnabled($module, $action);
    }

    public function isModuleEnabled($module, $action = 'view')
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $role = $this->userRole;
        $column = [
            'view' => 'can_view',
            'create' => 'can_create',
            'edit' => 'can_edit',
            'delete' => 'can_delete',
        ][$action] ?? null;
        if (!$this->role_id || !$column || !$role || !$role->is_active) {
            return false;
        }

        return DB::table('module_access')
            ->where('role_id', $this->role_id)
            ->where('module_name', $module)
            ->where($column, true)
            ->exists();
    }

    public static function moduleOptionsForRole($role)
    {
        return DB::table('module_access')
            ->distinct()
            ->orderBy('module_name')
            ->pluck('module_name', 'module_name')
            ->all();
    }

    public static function moduleActionsForRole($role, $module)
    {
        if (in_array($module, ['dashboard', 'employee_profile'], true)) {
            return ['view'];
        }

        return ['view', 'create', 'edit', 'delete'];
    }

    public function roleAllowsPermission($module, $action)
    {
        return $this->hasPermission($module, $action);
    }

    public function permissionsFor($module)
    {
        $permissions = [];
        foreach (self::moduleActionsForRole($this->role, $module) as $action) {
            $permissions[$action] = $this->hasPermission($module, $action);
        }

        return $permissions;
    }
}
