<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;

class ThrottleLoginAttempts
{
    private $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    public function handle($request, Closure $next)
    {
        $identity = strtolower(trim((string) $request->input('email')));
        $key = 'hrms-login:' . hash('sha256', $request->ip() . '|' . $identity);

        if ($this->limiter->tooManyAttempts($key, 5)) {
            return response()->json([
                'message' => 'Too many sign-in attempts for this account. Please wait before trying again.',
            ], 429)->header('Retry-After', $this->limiter->availableIn($key));
        }

        $response = $next($request);

        if ($response->getStatusCode() === 422) {
            $this->limiter->hit($key, 60);
        } elseif ($response->getStatusCode() < 400) {
            $this->limiter->clear($key);
        }

        return $response;
    }
}
