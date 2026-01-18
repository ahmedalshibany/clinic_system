<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display a listing of users with search and filters.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search by name or username
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Sort
        $sortColumn = $request->get('sort', 'id');
        $sortDirection = $request->get('direction', 'desc');
        $allowedSorts = ['id', 'name', 'username', 'role', 'is_active', 'created_at'];
        if (in_array($sortColumn, $allowedSorts)) {
            $query->orderBy($sortColumn, $sortDirection);
        }

        $users = $query->paginate(10)->withQueryString();

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username|alpha_dash',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'role' => 'required|in:admin,doctor,receptionist',
            'is_active' => 'boolean',
        ], [
            'name.required' => __('Name is required'),
            'username.required' => __('Username is required'),
            'username.unique' => __('This username is already taken'),
            'password.required' => __('Password is required'),
            'password.confirmed' => __('Passwords do not match'),
            'role.required' => __('Role is required'),
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);

        User::create($validated);

        return redirect()->route('users.index')
            ->with('success', __('User created successfully!'));
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username,' . $user->id . '|alpha_dash',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,doctor,receptionist',
            'is_active' => 'boolean',
        ]);

        // Update password if provided
        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Password::min(8)->letters()->numbers()],
            ]);
            $validated['password'] = Hash::make($request->password);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $user->update($validated);

        return redirect()->route('users.index')
            ->with('success', __('User updated successfully!'));
    }

    /**
     * Toggle user active status.
     */
    public function toggleActive(User $user)
    {
        // Prevent self-deactivation
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => __('You cannot deactivate your own account.')]);
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? __('activated') : __('deactivated');
        return back()->with('success', __('User :status successfully.', ['status' => $status]));
    }

    /**
     * Reset user password (admin action).
     */
    public function resetPassword(User $user)
    {
        // Generate a random password
        $newPassword = Str::random(10);
        $user->update(['password' => Hash::make($newPassword)]);

        // In production, you would send this via email
        return back()->with('success', __('Password reset successfully. New password: :password', ['password' => $newPassword]));
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => __('You cannot delete your own account.')]);
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', __('User deleted successfully!'));
    }
}
