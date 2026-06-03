<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Services\DoctorService;
use App\Http\Requests\Doctor\StoreDoctorRequest;
use App\Http\Requests\Doctor\UpdateDoctorRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;

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
        $this->authorize('viewAny', Doctor::class);
        $doctors = $this->doctorService->getAllDoctors($request->all());
        return view('doctors.index', compact('doctors'));
    }

    public function create()
    {
        $this->authorize('create', Doctor::class);
        return view('doctors.create');
    }

    public function store(StoreDoctorRequest $request)
    {
        $this->authorize('create', Doctor::class);
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

    public function show(Doctor $doctor)
    {
        $this->authorize('view', $doctor);
        $doctor->load('appointments.patient');
        return view('doctors.show', compact('doctor'));
    }

    public function edit(Doctor $doctor)
    {
        $this->authorize('update', $doctor);
        return view('doctors.edit', compact('doctor'));
    }

    public function update(UpdateDoctorRequest $request, Doctor $doctor)
    {
        $this->authorize('update', $doctor);
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

    public function destroy(Doctor $doctor)
    {
        $this->authorize('delete', $doctor);
        try {
            $this->doctorService->deleteDoctor($doctor);
            return redirect()->route('doctors.index')
                ->with('success', __('Doctor deleted successfully!'));
        } catch (\Exception $e) {
             Log::error('Failed to delete doctor: ' . $e->getMessage());
             return redirect()->route('doctors.index')
                 ->with('error', $e->getMessage());
        }
    }

}
