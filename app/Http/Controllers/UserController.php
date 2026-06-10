<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', User::class);

        $users = User::paginate(10);

        if (view()->exists('users.index')) {
            return view('users.index', compact('users'));
        }
        return response()->json($users);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', User::class);

        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'nullable|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,doctor,receptionist,nurse',
            'password' => 'required|string|min:6',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        // is_active defaults to true in DB, but let's be explicit if needed, or let DB handle default.
        // Model has default in migration usually, but let's add it if not in validation defaults.
        // The prompt asked to "Explicitly define $fillable with ALL columns... included is_active".
        // It didn't say validate is_active, but standard practice is 1.
        $validated['is_active'] = true;

        User::create($validated);

        return redirect()->route('users.index')
                        ->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,doctor,receptionist,nurse',
            'is_active' => 'boolean',
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:6']);
            $validated['password'] = Hash::make($request->password);
        }

        $user->update($validated);

        return redirect()->route('users.index')
                        ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        try {
            $user->delete();
        } catch (QueryException $e) {
            return back()->with('error', 'Cannot delete this user because they have related records (appointments, vitals, etc.). Deactivate the user instead.');
        }

        return redirect()->route('users.index')
                        ->with('success', 'User deleted successfully.');
    }

    public function toggleActive(User $user)
    {
        $this->authorize('toggleActive', $user);

        $user->update([
            'is_active' => !$user->is_active
        ]);

        return redirect()->back()->with('success', __('messages.user_status_updated'));
    }

    public function resetPassword(User $user)
    {
        $this->authorize('resetPassword', $user);

        $defaultPassword = 'Password123!';

        $user->update([
            'password' => Hash::make($defaultPassword),
            'password_change_required' => true,
        ]);

        return redirect()->back()->with('success', __('messages.user_password_reset_success'));
    }
}
