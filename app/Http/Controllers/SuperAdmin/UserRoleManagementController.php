<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\UserRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserRoleManagementController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['status' => 'nullable|in:active,inactive']);
        $status = $request->input('status', 'active');
        $roles = UserRole::withCount('users')
            ->where('is_active', $status === 'active')
            ->orderBy('name')
            ->get();

        return view('portal.security.user-roles', [
            'roles' => $roles,
            'selectedStatus' => $status,
            'activeRoleCount' => UserRole::where('is_active', true)->count(),
            'inactiveRoleCount' => UserRole::where('is_active', false)->count(),
            'pageTitle' => 'User roles',
        ]);
    }

    public function store(Request $request)
    {
        $attributes = $request->validate([
            'name' => [
                'required',
                'string',
                'max:30',
                'regex:/^[a-z][a-z0-9_]*$/',
                'unique:user_roles,name',
                Rule::notIn(['admin', 'super_admin']),
            ],
        ]);

        DB::transaction(function () use ($attributes) {
            $role = UserRole::create(['name' => $attributes['name'], 'is_active' => true]);
            $moduleNames = DB::table('module_access')->distinct()->orderBy('module_name')->pluck('module_name');

            foreach ($moduleNames as $moduleName) {
                $role->moduleAccess()->create([
                    'module_name' => $moduleName,
                    'can_view' => false,
                    'can_create' => false,
                    'can_edit' => false,
                    'can_delete' => false,
                ]);
            }
        });

        return redirect()
            ->route('portal.user-roles.index')
            ->with('status', 'User role created. Configure its module access before assigning users.');
    }

    public function update(Request $request, UserRole $userRole)
    {
        abort_if(in_array($userRole->name, ['admin', 'super_admin'], true), 404);

        $attributes = $request->validate([
            'name' => [
                'required',
                'string',
                'max:30',
                'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('user_roles', 'name')->ignore($userRole->id),
                Rule::notIn(['admin', 'super_admin']),
            ],
        ]);

        $userRole->update($attributes);

        return redirect()
            ->route('portal.user-roles.index')
            ->with('status', 'User role updated.');
    }

    public function updateStatus(Request $request, UserRole $userRole)
    {
        abort_if(in_array($userRole->name, ['admin', 'super_admin'], true), 404);

        $attributes = $request->validate(['is_active' => 'required|boolean']);
        $userRole->update($attributes);

        return redirect()
            ->route('portal.user-roles.index', ['status' => $userRole->is_active ? 'active' : 'inactive'])
            ->with('status', $userRole->is_active ? 'User role activated.' : 'User role deactivated.');
    }
}
