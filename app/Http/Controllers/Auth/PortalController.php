<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalController extends Controller
{
    private $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    public function entry()
    {
        return Auth::check()
            ? redirect()->route('portal.dashboard')
            : redirect()->route('login');
    }

    public function loginForm()
    {
        if (Auth::check()) {
            return redirect()->route('portal.dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email|max:190',
            'password' => 'required|string|max:200',
        ]);
        $key = 'hrms-web-login:' . hash('sha256', $request->ip() . '|' . strtolower(trim($credentials['email'])));

        if ($this->limiter->tooManyAttempts($key, 5)) {
            return back()->withErrors([
                'email' => 'Too many sign-in attempts. Please wait before trying again.',
            ])->withInput($request->except('password'));
        }

        $attempt = [
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'is_active' => true,
        ];

        if (!Auth::attempt($attempt)) {
            $this->limiter->hit($key, 60);

            return back()->withErrors([
                'email' => 'These sign-in details do not match an active account.',
            ])->withInput($request->except('password'));
        }

        $user = Auth::user();
        if (!$user->userRole || !$user->userRole->is_active) {
            Auth::logout();
            $this->limiter->hit($key, 60);

            return back()->withErrors([
                'email' => 'This account role is inactive. Please contact your system administrator.',
            ])->withInput($request->except('password'));
        }

        if ($user->role === 'employee' && !$user->employee) {
            Auth::logout();
            $this->limiter->hit($key, 60);

            return back()->withErrors([
                'email' => 'This employee account is not linked to an employee record. Please contact your HR administrator.',
            ])->withInput($request->except('password'));
        }

        if ($user->employee && (!$user->employee->is_active || $user->employee->employment_status === 'inactive')) {
            Auth::logout();
            $this->limiter->hit($key, 60);

            return back()->withErrors([
                'email' => 'This employee account is inactive. Please contact your HR administrator.',
            ])->withInput($request->except('password'));
        }

        $this->limiter->clear($key);
        $request->session()->regenerate();

        return redirect()->intended(route('portal.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
