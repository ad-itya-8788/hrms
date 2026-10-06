<?php

namespace App\Http\Middleware;

use Closure;

class RequireSuperAdmin
{
    public function handle($request, Closure $next)
    {
        if (!$request->user() || !$request->user()->isSuperAdmin()) {
            abort(403);
        }

        return $next($request);
    }
}
