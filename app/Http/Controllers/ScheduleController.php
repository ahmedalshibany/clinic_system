<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\DoctorLeave;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    /**
     * Display the doctor's schedule.
     */
    public function show(Doctor $doctor)
    {
        $schedules = $doctor->schedules()->get()->keyBy('day_of_week');
        $leaves = $doctor->leaves()->where('end_date', '>=', now())->orderBy('start_date')->get();
        
        return view('doctors.schedule', compact('doctor', 'schedules', 'leaves'));
    }

    /**
     * Update the doctor's schedule.
     */
    public function update(Request $request, Doctor $doctor)
    {
        $validated = $request->validate([
            'schedules' => 'array',
            'schedules.*.day_of_week' => 'required|integer|min:0|max:6',
            'schedules.*.start_time' => 'required|date_format:H:i',
            'schedules.*.end_time' => 'required|date_format:H:i|after:schedules.*.start_time',
            'schedules.*.slot_duration' => 'required|integer|min:5|max:120',
            'schedules.*.max_appointments' => 'nullable|integer|min:1',
            'schedules.*.is_active' => 'boolean',
        ]);

        foreach ($request->schedules as $data) {
            $doctor->schedules()->updateOrCreate(
                ['day_of_week' => $data['day_of_week']],
                [
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'slot_duration' => $data['slot_duration'],
                    'max_appointments' => $data['max_appointments'] ?? null,
                    'is_active' => $data['is_active'] ?? false,
                ]
            );
        }

        return back()->with('success', __('Schedule updated successfully!'));
    }

    /**
     * Get available time slots for a specific date.
     */
    public function getAvailableSlots(Doctor $doctor, $date)
    {
        $date = Carbon::parse($date);
        $dayOfWeek = $date->dayOfWeek; // 0 (Sunday) to 6 (Saturday)

        // 1. Check if doctor is on leave
        $isOnLeave = $doctor->leaves()
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->exists();

        if ($isOnLeave) {
            return response()->json(['slots' => [], 'message' => 'Doctor is on leave']);
        }

        // 2. Get schedule for this day
        $schedule = $doctor->schedules()
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->first();

        if (!$schedule) {
            return response()->json(['slots' => [], 'message' => 'No schedule for this day']);
        }

        // 3. Generate all possible slots
        $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $schedule->start_time);
        $endTime = Carbon::parse($date->format('Y-m-d') . ' ' . $schedule->end_time);
        $slotDuration = $schedule->slot_duration; // minutes

        $allSlots = [];
        $currentSlot = $startTime->copy();

        while ($currentSlot->copy()->addMinutes($slotDuration)->lte($endTime)) {
            $allSlots[] = $currentSlot->format('H:i');
            $currentSlot->addMinutes($slotDuration);
        }

        // 4. Get booked appointments
        $bookedSlots = $doctor->appointments()
            ->whereDate('date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->pluck('time')
            ->map(function ($time) {
                return Carbon::parse($time)->format('H:i');
            })
            ->toArray();

        // 5. Filter available slots
        $availableSlots = array_values(array_diff($allSlots, $bookedSlots));

        return response()->json([
            'slots' => $availableSlots, 
            'schedule' => $schedule,
            'booked' => $bookedSlots
        ]);
    }

    /**
     * Store a new leave for the doctor.
     */
    public function storeLeave(Request $request, Doctor $doctor)
    {
        $validated = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:255',
        ]);

        $doctor->leaves()->create($validated);

        return back()->with('success', __('Leave added successfully!'));
    }

    /**
     * Delete a doctor's leave.
     */
    public function destroyLeave(Doctor $doctor, DoctorLeave $leave)
    {
        if ($leave->doctor_id !== $doctor->id) {
            abort(403);
        }

        $leave->delete();

        return back()->with('success', __('Leave deleted successfully!'));
    }
}
