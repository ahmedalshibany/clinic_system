<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Show login form.
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle user login with username.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Find user by username
        $user = User::where('username', $credentials['username'])->first();

        if (!$user) {
            return back()->withErrors([
                'username' => 'Invalid username or password'
            ])->withInput($request->only('username'));
        }

        // Check if user is active
        if (!$user->is_active) {
            return back()->withErrors([
                'username' => 'Your account has been deactivated'
            ])->withInput($request->only('username'));
        }

        // Verify password
        if (!Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'username' => 'Invalid username or password'
            ])->withInput($request->only('username'));
        }

        // Login the user
        Auth::login($user, $request->boolean('remember'));

        // Regenerate session
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
