<?php

namespace App\Http\Middleware;

use Closure;

class RequirePermission
{
    public function handle($request, Closure $next, $module, $action = 'view')
    {
        if (!$request->user() || !$request->user()->hasPermission($module, $action)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'You do not have permission to perform this action.'], 403);
            }

            abort(403, 'You do not have permission to access this area.');
        }

        return $next($request);
    }
}
