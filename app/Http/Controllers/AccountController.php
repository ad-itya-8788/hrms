<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AccountController extends Controller
{
    public function edit(Request $request)
    {
        return view('account.password', [
            'pageTitle' => 'My account',
            'user' => $request->user(),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $attributes = $request->validate([
            'current_password' => 'required|string|max:200',
            'password' => 'required|string|min:8|max:72|confirmed',
        ]);
        $user = $request->user();

        if (!Hash::check($attributes['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'Your current password is incorrect.',
            ]);
        }

        if (Hash::check($attributes['password'], $user->password)) {
            return back()->withErrors([
                'password' => 'Choose a password different from your current password.',
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($attributes['password']),
            'remember_token' => Str::random(60),
        ])->save();

        return redirect()->route('portal.account.edit')
            ->with('status', 'Your password has been changed successfully.');
    }
}
