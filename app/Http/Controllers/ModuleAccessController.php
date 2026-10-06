<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ModuleAccessController extends Controller
{
    public function index(Request $request)
    {
        $roles = UserRole::withCount('users')
            ->orderBy('name')
            ->get();
        abort_if($roles->isEmpty(), 404, 'No user roles are available.');
        $request->validate([
            'role' => ['nullable', Rule::in($roles->pluck('name')->all())],
        ]);
        $selectedRole = $request->input('role', $roles->first()->name);
        $role = UserRole::where('name', $selectedRole)->withCount('users')->with('moduleAccess')->firstOrFail();
        $isSystemRole = in_array($role->name, ['superadmin', 'super_admin', 'admin'], true);
        $canManageAccess = !$isSystemRole && $role->is_active;
        $modules = [];

        $moduleNames = DB::table('module_access')->distinct()->orderBy('module_name')->pluck('module_name');
        foreach ($moduleNames as $moduleName) {
            $access = $role->moduleAccess->firstWhere('module_name', $moduleName);
            $actions = User::moduleActionsForRole($role->name, $moduleName);
            $actionLabels = [
                'view' => 'View',
                'create' => 'Create',
                'edit' => 'Edit',
                'delete' => 'Activate / deactivate',
            ];
            $actionOptions = [];
            foreach ($actions as $action) {
                $column = 'can_' . $action;
                $actionOptions[] = [
                    'key' => $column,
                    'label' => $actionLabels[$action],
                    'enabled' => $isSystemRole ? true : ($access ? $access->$column : false),
                ];
            }
            $modules[] = [
                'key' => $moduleName,
                'label' => ucwords(str_replace('_', ' ', $moduleName)),
                'actions' => $actionOptions,
            ];
        }

        return view('portal.security.module-access', [
            'modules' => array_values($modules),
            'roles' => $roles,
            'selectedRole' => $selectedRole,
            'selectedRoleRecord' => $role,
            'isSystemRole' => $isSystemRole,
            'canManageAccess' => $canManageAccess,
            'pageTitle' => 'Module access',
        ]);
    }

    public function update(Request $request)
    {
        $roles = UserRole::whereNotIn('name', ['admin', 'super_admin', 'superadmin'])
            ->where('is_active', true)
            ->pluck('name')
            ->all();
        abort_if(empty($roles), 404, 'No configurable user roles are available.');
        $rules = [
            'role' => ['required', Rule::in($roles)],
            'access' => 'nullable|array',
        ];
        $moduleNames = DB::table('module_access')->distinct()->orderBy('module_name')->pluck('module_name');
        $actionsByModule = [];
        foreach ($moduleNames as $moduleName) {
            $rules['access.' . $moduleName] = 'nullable|array';
            $actionsByModule[$moduleName] = array_map(function ($action) {
                return 'can_' . $action;
            }, User::moduleActionsForRole($request->input('role'), $moduleName));
            $rules['access.' . $moduleName . '.*'] = 'nullable|in:1';
            foreach ($actionsByModule[$moduleName] as $action) {
                $rules['access.' . $moduleName . '.' . $action] = 'nullable|in:1';
            }
        }
        $validated = $request->validate($rules);
        $access = $validated['access'] ?? [];
        $roleName = $validated['role'];

        DB::transaction(function () use ($access, $roleName, $moduleNames, $actionsByModule) {
            $role = UserRole::where('name', $roleName)->firstOrFail();
            foreach ($moduleNames as $moduleName) {
                $values = [
                    'can_view' => false,
                    'can_create' => false,
                    'can_edit' => false,
                    'can_delete' => false,
                ];
                foreach ($actionsByModule[$moduleName] as $action) {
                    $values[$action] = isset($access[$moduleName][$action]);
                }
                $role->moduleAccess()->updateOrCreate(
                    ['module_name' => $moduleName],
                    $values
                );
            }
        });

        return redirect()->route('portal.module-access.index', ['role' => $roleName])->with('status', 'Module access settings saved.');
    }

}
