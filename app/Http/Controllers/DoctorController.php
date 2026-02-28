<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Services\DoctorService;
use App\Http\Requests\Doctor\StoreDoctorRequest;
use App\Http\Requests\Doctor\UpdateDoctorRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DoctorController extends Controller
{
    protected DoctorService $doctorService;

    public function __construct(DoctorService $doctorService)
    {
        $this->doctorService = $doctorService;
    }

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
    public function store(StoreDoctorRequest $request)
    {
        try {
            $this->doctorService->createDoctor($request->validated());

            return redirect()->route('doctors.index')
                ->with('success', __('Doctor added successfully!'));
        } catch (\Exception $e) {
            Log::error('Failed to create doctor: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', __('Failed to create doctor. Please try again.'));
        }
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
    public function update(UpdateDoctorRequest $request, Doctor $doctor)
    {
        try {
            $this->doctorService->updateDoctor($doctor, $request->validated());

            return redirect()->route('doctors.index')
                ->with('success', __('Doctor updated successfully!'));
        } catch (\Exception $e) {
            Log::error('Failed to update doctor: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', __('Failed to update doctor. Please try again.'));
        }
    }

    /**
     * Remove the specified doctor.
     */
    public function destroy(Doctor $doctor)
    {
        try {
            $doctor->delete();

            return redirect()->route('doctors.index')
                ->with('success', __('Doctor deleted successfully!'));
        } catch (\Exception $e) {
             Log::error('Failed to delete doctor: ' . $e->getMessage());
             return redirect()->route('doctors.index')
                 ->with('error', __('Cannot delete. This doctor may have existing records.'));
        }
    }
}
