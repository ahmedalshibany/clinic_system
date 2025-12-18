<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle user login with username.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Find user by username
        $user = User::where('username', $credentials['username'])->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid username or password'
            ], 401);
        }

        // Check if user is active
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated'
            ], 403);
        }

        // Verify password
        if (!Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid username or password'
            ], 401);
        }

        // Login the user
        Auth::login($user, $request->boolean('remember'));

        // Regenerate session
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'role' => $user->role,
            ],
            'redirect' => route('dashboard')
        ]);
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
            'redirect' => route('login')
        ]);
    }

    /**
     * Get current authenticated user.
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'role' => $user->role,
            ]
        ]);
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 422);
        }

        $user->update([
            'password' => Hash::make($validated['password'])
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully'
        ]);
    }

    /**
     * Update username.
     */
    public function updateUsername(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => 'required|string|min:3|unique:users,username,' . $request->user()->id,
            'password' => 'required|string',
        ]);

        $user = $request->user();

        if (!Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password is incorrect'
            ], 422);
        }

        $user->update([
            'username' => $validated['username']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Username updated successfully'
        ]);
    }
}
