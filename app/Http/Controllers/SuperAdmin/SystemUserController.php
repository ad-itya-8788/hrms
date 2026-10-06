<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SystemUserController extends Controller
{
    public function index(Request $request)
    {
        $attributes = $request->validate([
            'status' => 'nullable|in:active,inactive',
            'search' => 'nullable|string|max:100',
            'page' => 'nullable|integer|min:1',
        ]);
        $status = $attributes['status'] ?? 'active';
        $search = trim($attributes['search'] ?? '');
        $viewData = [
            'users' => $this->usersQuery($status, $search)->orderBy('name')->paginate(25)->appends($request->only('status', 'search')),
            'selectedStatus' => $status,
            'search' => $search,
            'activeUserCount' => User::where('is_active', true)->count(),
            'inactiveUserCount' => User::where('is_active', false)->count(),
            'passwordUrl' => route('portal.system-users.password', ['user' => '__USER__']),
            'pageTitle' => 'System users',
        ];

        return view('portal.security.system-users', $viewData);
    }

    public function data(Request $request)
    {
        $attributes = $request->validate([
            'status' => 'required|in:active,inactive',
            'search' => 'nullable|string|max:100',
            'page' => 'nullable|integer|min:1',
        ]);
        $status = $attributes['status'];
        $search = trim($attributes['search'] ?? '');
        $users = $this->usersQuery($status, $search)->orderBy('name')->paginate(25)->appends([
            'status' => $status,
            'search' => $search,
        ]);

        return response()->json([
            'html' => view('portal.security.system-user-rows', [
                'users' => $users,
                'selectedStatus' => $status,
            ])->render(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'total' => $users->total(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ],
            'counts' => [
                'active' => User::where('is_active', true)->count(),
                'inactive' => User::where('is_active', false)->count(),
            ],
        ]);
    }

    private function usersQuery($status, $search)
    {
        $query = User::with(['userRole', 'employee'])
            ->where('is_active', $status === 'active');
        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhereHas('userRole', function ($roleQuery) use ($search) {
                        $roleQuery->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('employee', function ($employeeQuery) use ($search) {
                        $employeeQuery->where('employee_code', 'like', '%' . $search . '%')
                            ->orWhere('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%');
                    });
            });
        }

        return $query;
    }

    public function updateStatus(Request $request, User $user)
    {
        $attributes = $request->validate(['is_active' => 'required|boolean']);
        $isActive = (bool) $attributes['is_active'];

        if (!$isActive && (int) $request->user()->id === (int) $user->id) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'You cannot deactivate your own account.'], 422);
            }

            return back()->withErrors(['user' => 'You cannot deactivate your own account.']);
        }

        $updated = DB::transaction(function () use ($user, $isActive) {
            $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $roleName = $lockedUser->userRole ? $lockedUser->userRole->name : null;
            if (!$isActive && $lockedUser->is_active && in_array($roleName, ['admin', 'super_admin'], true)) {
                $activeAdministrators = User::where('is_active', true)
                    ->whereHas('userRole', function ($query) {
                        $query->whereIn('name', ['admin', 'super_admin']);
                    })
                    ->lockForUpdate()
                    ->count();

                if ($activeAdministrators <= 1) {
                    return false;
                }
            }

            $lockedUser->update(['is_active' => $isActive]);

            return true;
        });

        if (!$updated) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'The last active administrator account cannot be deactivated.'], 422);
            }

            return back()->withErrors(['user' => 'The last active administrator account cannot be deactivated.']);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $isActive ? 'User account activated.' : 'User account deactivated.',
            ]);
        }

        return redirect()
            ->route('portal.system-users.index', ['status' => $isActive ? 'active' : 'inactive'])
            ->with('status', $isActive ? 'User account activated.' : 'User account deactivated.');
    }

    public function updatePassword(Request $request, User $user)
    {
        $attributes = $request->validate([
            'password' => 'required|string|min:8|max:72|confirmed',
        ]);

        $user->forceFill([
            'password' => Hash::make($attributes['password']),
            'remember_token' => Str::random(60),
        ])->save();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Password changed for ' . $user->name . '.']);
        }

        return redirect()
            ->route('portal.system-users.index', ['status' => $user->is_active ? 'active' : 'inactive'])
            ->with('status', 'Password changed for ' . $user->name . '.');
    }
}
