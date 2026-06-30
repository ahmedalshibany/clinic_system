<?php

namespace App\Http\Controllers;

use App\Models\LoginAttempt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    const MAX_LOGIN_ATTEMPTS = 5;
    const LOCKOUT_DURATION_MINUTES = 15;

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = $credentials['username'];

        $lockoutUntil = $this->isLocked($username);
        if ($lockoutUntil) {
            $remaining = now()->diffInMinutes($lockoutUntil, true);
            return back()->withErrors([
                'username' => trans('auth.throttle', [
                    'minutes' => max(1, (int) ceil($remaining)),
                ]),
            ])->withInput($request->only('username'));
        }

        $user = User::where('username', $username)->first();

        $authFailed = !$user || !$user->is_active || !Hash::check($credentials['password'], $user->password);

        if ($authFailed) {
            LoginAttempt::create([
                'username' => $username,
                'ip_address' => $request->ip(),
                'success' => false,
            ]);

            Log::warning('Failed login attempt', [
                'username' => $username,
                'ip' => $request->ip(),
            ]);

            $lockoutUntil = $this->isLocked($username);
            if ($lockoutUntil) {
                $remaining = now()->diffInMinutes($lockoutUntil, true);
                return back()->withErrors([
                    'username' => trans('auth.throttle', [
                        'minutes' => max(1, (int) ceil($remaining)),
                    ]),
                ])->withInput($request->only('username'));
            }

            return back()->withErrors([
                'username' => trans('auth.failed'),
            ])->withInput($request->only('username'));
        }

        LoginAttempt::where('username', $username)->delete();

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        Log::info('Successful login', [
            'username' => $username,
            'user_id' => $user->id,
            'ip' => $request->ip(),
        ]);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function isLocked(string $username): ?Carbon
    {
        $cutoff = now()->subMinutes(self::LOCKOUT_DURATION_MINUTES);

        $recentFailures = LoginAttempt::where('username', $username)
            ->where('success', false)
            ->where('created_at', '>=', $cutoff)
            ->count();

        if ($recentFailures >= self::MAX_LOGIN_ATTEMPTS) {
            $oldest = LoginAttempt::where('username', $username)
                ->where('success', false)
                ->where('created_at', '>=', $cutoff)
                ->orderBy('created_at', 'asc')
                ->value('created_at');

            return $oldest->copy()->addMinutes(self::LOCKOUT_DURATION_MINUTES);
        }

        return null;
    }
}
