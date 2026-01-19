<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Display a listing of appointments.
     */
    public function index(Request $request)
    {
        $query = Appointment::with(['patient', 'doctor']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('patient', function ($pq) use ($search) {
                    $pq->where('name', 'like', "%{$search}%");
                })->orWhereHas('doctor', function ($dq) use ($search) {
                    $dq->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        // Sort
        $sortColumn = $request->get('sort', 'date');
        $sortDirection = $request->get('direction', 'desc');
        
        if ($sortColumn === 'patient') {
            $query->join('patients', 'appointments.patient_id', '=', 'patients.id')
                  ->orderBy('patients.name', $sortDirection)
                  ->select('appointments.*');
        } elseif ($sortColumn === 'doctor') {
            $query->join('doctors', 'appointments.doctor_id', '=', 'doctors.id')
                  ->orderBy('doctors.name', $sortDirection)
                  ->select('appointments.*');
        } else {
            $query->orderBy($sortColumn, $sortDirection);
        }

        $appointments = $query->paginate(10)->withQueryString();
        $patients = Patient::orderBy('name')->get();
        $doctors = Doctor::where('is_active', true)->orderBy('name')->get();

        return view('appointments.index', compact('appointments', 'patients', 'doctors'));
    }

    /**
     * Show the form for creating a new appointment.
     */
    public function create()
    {
        $patients = Patient::orderBy('name')->get();
        $doctors = Doctor::where('is_active', true)->orderBy('name')->get();
        return view('appointments.create', compact('patients', 'doctors'));
    }

    /**
     * Store a newly created appointment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|string',
            'type' => 'required|in:Consultation,Checkup,Follow-up,Emergency',
            'status' => 'required|in:scheduled,confirmed,waiting,in_progress,completed,cancelled,no_show',
            'notes' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'prescription' => 'nullable|string',
            'reason' => 'nullable|string',
            'fee' => 'nullable|numeric|min:0',
        ]);

        $doctor = Doctor::find($validated['doctor_id']);
        
        // 1. Check if doctor is on leave
        // Only check if it's not a past date (to allow historical data entry if needed)
        if (\Carbon\Carbon::parse($validated['date'])->isFuture()) {
            $isOnLeave = $doctor->leaves()
                ->whereDate('start_date', '<=', $validated['date'])
                ->whereDate('end_date', '>=', $validated['date'])
                ->exists();

            if ($isOnLeave) {
                return back()->withErrors(['date' => __('Doctor is on leave on this date.')])->withInput();
            }

            // 2. Check doctor's schedule
            $dayOfWeek = \Carbon\Carbon::parse($validated['date'])->dayOfWeek;
            $schedule = $doctor->schedules()->where('day_of_week', $dayOfWeek)->where('is_active', true)->first();

            if (!$schedule) {
                return back()->withErrors(['date' => __('Doctor is not available on this day.')])->withInput();
            }

            // 3. Check time slot validity (within working hours)
            $apptTime = \Carbon\Carbon::parse($validated['date'] . ' ' . $validated['time']);
            $startTime = \Carbon\Carbon::parse($validated['date'] . ' ' . $schedule->start_time);
            $endTime = \Carbon\Carbon::parse($validated['date'] . ' ' . $schedule->end_time);

            if ($apptTime->lt($startTime) || $apptTime->gte($endTime)) {
                return back()->withErrors(['time' => __('Selected time is outside doctor\'s working hours.')])->withInput();
            }
        }

        // 4. Check for conflicts
        $conflict = Appointment::where('doctor_id', $validated['doctor_id'])
            ->where('date', $validated['date'])
            ->where('time', $validated['time'])
            ->whereNotIn('status', ['cancelled'])
            ->exists();

        if ($conflict) {
            return back()->withErrors([
                'time' => __('This time slot is already booked for this doctor.')
            ])->withInput();
        }

        Appointment::create($validated);

        return redirect()->route('appointments.index')
            ->with('success', __('Appointment booked successfully!'));
    }

    /**
     * Display the specified appointment.
     */
    public function show(Appointment $appointment)
    {
        $appointment->load(['patient', 'doctor']);
        return view('appointments.show', compact('appointment'));
    }

    /**
     * Show the form for editing the specified appointment.
     */
    public function edit(Appointment $appointment)
    {
        $patients = Patient::orderBy('name')->get();
        $doctors = Doctor::where('is_active', true)->orderBy('name')->get();
        return view('appointments.edit', compact('appointment', 'patients', 'doctors'));
    }

    /**
     * Update the specified appointment.
     */
    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'date' => 'required|date',
            'time' => 'required|string',
            'type' => 'required|in:Consultation,Checkup,Follow-up,Emergency',
            'status' => 'required|in:scheduled,confirmed,waiting,in_progress,completed,cancelled,no_show',
            'notes' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'prescription' => 'nullable|string',
            'reason' => 'nullable|string',
            'fee' => 'nullable|numeric|min:0',
        ]);

        $doctor = Doctor::find($validated['doctor_id']);

        // Check availability logic only if date/time/doctor changed
        $timeChanged = $validated['date'] !== $appointment->date->format('Y-m-d') || 
                       $validated['time'] !== $appointment->time->format('H:i') ||
                       $validated['doctor_id'] != $appointment->doctor_id;

        if ($timeChanged && \Carbon\Carbon::parse($validated['date'])->isFuture()) {
            
            // 1. Check leave
            $isOnLeave = $doctor->leaves()
                ->whereDate('start_date', '<=', $validated['date'])
                ->whereDate('end_date', '>=', $validated['date'])
                ->exists();

            if ($isOnLeave) {
                return back()->withErrors(['date' => __('Doctor is on leave on this date.')])->withInput();
            }
            
            // 2. Check schedule 
            $dayOfWeek = \Carbon\Carbon::parse($validated['date'])->dayOfWeek;
            $schedule = $doctor->schedules()->where('day_of_week', $dayOfWeek)->where('is_active', true)->first();

            if (!$schedule) {
                return back()->withErrors(['date' => __('Doctor is not available on this day.')])->withInput();
            }

             // 3. Check time slot validity (within working hours)
            $apptTime = \Carbon\Carbon::parse($validated['date'] . ' ' . $validated['time']);
            $startTime = \Carbon\Carbon::parse($validated['date'] . ' ' . $schedule->start_time);
            $endTime = \Carbon\Carbon::parse($validated['date'] . ' ' . $schedule->end_time);

            if ($apptTime->lt($startTime) || $apptTime->gte($endTime)) {
                return back()->withErrors(['time' => __('Selected time is outside doctor\'s working hours.')])->withInput();
            }
        }

        // 4. Check for conflicts (excluding current appointment)
        if ($timeChanged) {
            $conflict = Appointment::where('doctor_id', $validated['doctor_id'])
                ->where('date', $validated['date'])
                ->where('time', $validated['time'])
                ->where('id', '!=', $appointment->id)
                ->whereNotIn('status', ['cancelled'])
                ->exists();

            if ($conflict) {
                return back()->withErrors([
                    'time' => __('This time slot is already booked for this doctor.')
                ])->withInput();
            }
        }

        $appointment->update($validated);

        return redirect()->route('appointments.index')
            ->with('success', __('Appointment updated successfully!'));
    }

    /**
     * Remove the specified appointment.
     */
    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return redirect()->route('appointments.index')
            ->with('success', __('Appointment deleted successfully!'));
    }

    // Appointment Lifecycle Methods

    public function checkIn(Appointment $appointment)
    {
        $appointment->update([
            'status' => 'waiting',
            'checked_in_at' => now(),
        ]);
        return back()->with('success', 'Patient checked in successfully.');
    }

    public function startVisit(Appointment $appointment)
    {
        $appointment->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
        return back()->with('success', 'Visit started.');
    }

    public function complete(Appointment $appointment)
    {
        $appointment->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
        return back()->with('success', 'Appointment completed.');
    }

    public function markNoShow(Appointment $appointment)
    {
        $appointment->update([
            'status' => 'no_show',
        ]);
        return back()->with('success', 'Marked as No Show.');
    }

    /**
     * Display the appointments calendar.
     */
    public function calendar()
    {
        $doctors = Doctor::where('is_active', true)->orderBy('name')->get();
        return view('appointments.calendar', compact('doctors'));
    }

    /**
     * Get appointments for calendar JSON API.
     */
    public function events(Request $request)
    {
        $query = Appointment::with(['patient', 'doctor']);

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->filled('start') && $request->filled('end')) {
            $query->whereBetween('date', [$request->start, $request->end]);
        }

        $appointments = $query->get()->map(function ($appt) {
            $colors = [
                'scheduled' => '#3b82f6', // blue
                'confirmed' => '#10b981', // green
                'waiting' => '#f59e0b',   // orange
                'in_progress' => '#8b5cf6', // purple
                'completed' => '#6b7280', // gray
                'cancelled' => '#ef4444', // red
                'no_show' => '#000000',   // black
                'pending' => '#64748b',   // slate
            ];

            return [
                'id' => $appt->id,
                'title' => $appt->patient->name . ' (' . $appt->type . ')',
                'start' => $appt->date->format('Y-m-d') . 'T' . $appt->time->format('H:i:s'),
                'backgroundColor' => $colors[$appt->status] ?? '#3b82f6',
                'borderColor' => $colors[$appt->status] ?? '#3b82f6',
                'extendedProps' => [
                    'doctor_name' => $appt->doctor->name,
                    'patient_name' => $appt->patient->name,
                    'status' => ucfirst(str_replace('_', ' ', $appt->status)),
                    'type' => $appt->type,
                ],
                'url' => route('appointments.show', $appt->id)
            ];
        });

        return response()->json($appointments);
    }

    /**
     * Display the appointments queue for today.
     */
    public function queue(Request $request)
    {
        $query = Appointment::with(['patient', 'doctor'])
            ->whereDate('date', now()->today());

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        $appointments = $query->orderBy('time')->get();

        $doctors = Doctor::where('is_active', true)->orderBy('name')->get();

        return view('appointments.queue', compact('appointments', 'doctors'));
    }
}
