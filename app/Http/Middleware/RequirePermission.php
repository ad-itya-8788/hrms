<?php

namespace App\Http\Middleware;

use Closure;

class RequirePermission
{
    public function handle($request, Closure $next, $module, $action = 'view')
    {
        $user = $request->user();
        $departmentHeadCanView = $user
            && $action === 'view'
            && in_array($module, ['employees', 'leaves', 'exit_pass'], true)
            && $user->isDepartmentHead();

        if (!$user || (!$user->hasPermission($module, $action) && !$departmentHeadCanView)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'You do not have permission to perform this action.'], 403);
            }

            abort(403, 'You do not have permission to access this area.');
        }

        return $next($request);
    }
}
