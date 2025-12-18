<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    /**
     * Display a listing of doctors.
     */
    public function index(Request $request)
    {
        $query = Doctor::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('specialty', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by active status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Sort
        $sortColumn = $request->get('sort', 'id');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortColumn, $sortDirection);

        $doctors = $query->paginate(8)->withQueryString();

        return view('doctors.index', compact('doctors'));
    }

    /**
     * Show the form for creating a new doctor.
     */
    public function create()
    {
        return view('doctors.create');
    }

    /**
     * Store a newly created doctor.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'specialty' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'bio' => 'nullable|string',
            'working_days' => 'nullable|array',
            'work_start_time' => 'nullable|string',
            'work_end_time' => 'nullable|string',
            'consultation_fee' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        // Set default values
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['working_days'] = $request->input('working_days', []);

        Doctor::create($validated);

        return redirect()->route('doctors.index')
            ->with('success', __('Doctor added successfully!'));
    }

    /**
     * Display the specified doctor.
     */
    public function show(Doctor $doctor)
    {
        $doctor->load('appointments.patient');
        return view('doctors.show', compact('doctor'));
    }

    /**
     * Show the form for editing the specified doctor.
     */
    public function edit(Doctor $doctor)
    {
        return view('doctors.edit', compact('doctor'));
    }

    /**
     * Update the specified doctor.
     */
    public function update(Request $request, Doctor $doctor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'specialty' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'bio' => 'nullable|string',
            'working_days' => 'nullable|array',
            'work_start_time' => 'nullable|string',
            'work_end_time' => 'nullable|string',
            'consultation_fee' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['working_days'] = $request->input('working_days', []);

        $doctor->update($validated);

        return redirect()->route('doctors.index')
            ->with('success', __('Doctor updated successfully!'));
    }

    /**
     * Remove the specified doctor.
     */
    public function destroy(Doctor $doctor)
    {
        $doctor->delete();

        return redirect()->route('doctors.index')
            ->with('success', __('Doctor deleted successfully!'));
    }
}
