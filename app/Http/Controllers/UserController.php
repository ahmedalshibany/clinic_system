<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Simple index to prevent errors if visited
        $users = User::paginate(10);
        // If the view doesn't exist, we might want to return something else, but let's assume index exists or just return data for now
        // Or render the index view if it existed. The user mentioned index.blade.php.
        // Wait, I couldn't find index.blade.php earlier. I should probably create a placeholder or just return json/text if view is missing?
        // But the user's primary goal is CREATION.
        if (view()->exists('users.index')) {
            return view('users.index', compact('users'));
        }
        return response()->json($users); // Fallback
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
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
        if (auth()->id() === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        try {
            $user->delete();
        } catch (QueryException $e) {
            return back()->with('error', 'Cannot delete this user because they have related records (appointments, vitals, etc.). Deactivate the user instead.');
        }

        return redirect()->route('users.index')
                        ->with('success', 'User deleted successfully.');
    }
}
