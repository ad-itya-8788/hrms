<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
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
        return in_array($this->role, ['super_admin', 'admin'], true);
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
