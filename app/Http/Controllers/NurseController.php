<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Notification;
use App\Models\Vital;
use App\Services\AppointmentService;
use App\Services\VitalService;
use App\Http\Requests\Vitals\StoreVitalRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class NurseController extends Controller
{
    protected VitalService $vitalService;
    protected AppointmentService $appointmentService;

    public function __construct(VitalService $vitalService, AppointmentService $appointmentService)
    {
        $this->vitalService = $vitalService;
        $this->appointmentService = $appointmentService;
    }

    public function createVitals(Appointment $appointment)
    {
        $this->authorize('create', Vital::class);
        if (!in_array($appointment->status, ['pending', 'paid', 'checked_in']) && !$appointment->vitals_unlocked) {
            return back()->with('error', __('messages.vitalsNotAllowed'))
                ->with('warning', __('messages.vitalsLocked'));
        }
        return view('nurse.vitals.create', compact('appointment'));
    }

    public function storeVitals(StoreVitalRequest $request, Appointment $appointment)
    {
        $this->authorize('create', Vital::class);
        try {
            $this->vitalService->recordVitals($appointment, $request->validated());
            return redirect()->route('dashboard')
                ->with('success', __('messages.vitalsRecorded'));
        } catch (\Exception $e) {
            Log::error('Failed to record vitals: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', __('messages.vitalsFailed'));
        }
    }

    /**
     * Nurse check-in: routes a paid patient to the doctor's triage queue.
     */
    public function checkIn(Appointment $appointment)
    {
        $this->authorize('checkIn', $appointment);

        $this->appointmentService->updateStatus($appointment, 'checked_in');

        if (request()->expectsJson()) {
            $appointment->load(['patient:id,name,patient_code,phone', 'doctor:id,name']);
            return response()->json([
                'success' => true,
                'message' => __('messages.patientCheckedIn'),
                'appointment' => $appointment,
            ]);
        }

        return redirect()->route('dashboard')
            ->with('success', __('messages.patientCheckedIn'));
    }

    /**
     * AJAX: Save vitals from inline dashboard form.
     */
    public function storeVitalsAjax(StoreVitalRequest $request, Appointment $appointment)
    {
        $this->authorize('create', Vital::class);
        try {
            if (!in_array($appointment->status, ['pending', 'paid', 'checked_in']) && !$appointment->vitals_unlocked) {
                return response()->json(['success' => false, 'message' => __('messages.vitalsNotAllowed')], 422);
            }
            $this->vitalService->recordVitals($appointment, $request->validated());
            return response()->json(['success' => true, 'message' => __('messages.vitalsRecorded')]);
        } catch (\Exception $e) {
            Log::error('Failed to record vitals (AJAX): ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => __('messages.vitalsFailed')], 500);
        }
    }

    /**
     * Nurse marks an appointment as no-show.
     */
    public function markNoShow(Appointment $appointment)
    {
        $this->authorize('markNoShow', $appointment);

        $this->appointmentService->updateStatus($appointment, 'no_show');

        return redirect()->route('dashboard')
            ->with('success', __('messages.markedNoShow'));
    }

    /**
     * AJAX: Return today's triage queue (confirmed/checked_in) + notification data as JSON.
     */
    public function triageQueueApi(Request $request)
    {
        $this->authorize('viewAny', Appointment::class);

        $boardData = Cache::remember('board:nurse', 30, function () {
            $todayAppts = Appointment::with(['patient:id,name', 'doctor:id,name'])
                ->where('date', '>=', today())
                ->where('date', '<', today()->addDay())
                ->whereIn('status', ['paid', 'checked_in', 'waiting'])
                ->orderBy('time')
                ->get(['id', 'patient_id', 'doctor_id', 'time', 'status']);

            $vitalsUnlocked = Appointment::with(['patient:id,name', 'doctor:id,name'])
                ->where('date', '>=', today())
                ->where('date', '<', today()->addDay())
                ->where('vitals_unlocked', true)
                ->orderBy('time')
                ->get(['id', 'patient_id', 'doctor_id', 'time', 'status', 'vitals_unlocked']);

            $triageQueue = $todayAppts->whereIn('status', ['paid', 'checked_in'])->values();
            $waitingList = $todayAppts->where('status', 'waiting')->values();
            $vitalsQueue = $vitalsUnlocked;

            return [
                'triageQueue' => $triageQueue->map(fn($a) => [
                    'id'            => $a->id,
                    'time'          => $a->time->format('H:i'),
                    'patient_name'  => $a->patient->name,
                    'doctor_name'   => $a->doctor->name,
                    'status'        => $a->status,
                    'status_label'  => $a->status === 'checked_in' ? __('messages.checked_in') : __('messages.paid'),
                ]),
                'waitingList' => $waitingList->map(fn($a) => [
                    'id'            => $a->id,
                    'time'          => $a->time->format('H:i'),
                    'patient_name'  => $a->patient->name,
                    'doctor_name'   => $a->doctor->name,
                    'status'        => $a->status,
                ]),
                'vitalsQueue' => $vitalsQueue->map(fn($a) => [
                    'id'            => $a->id,
                    'time'          => $a->time->format('H:i'),
                    'patient_name'  => $a->patient->name,
                    'doctor_name'   => $a->doctor->name,
                    'status'        => $a->status,
                    'vitals_unlocked' => true,
                ]),
                'triageCount'  => $triageQueue->count(),
                'waitingCount' => $waitingList->count(),
                'vitalsCount'  => $vitalsQueue->count(),
            ];
        });

        // Notifications are per-user and fast (indexed by user_id, created_at)
        $notificationQuery = Notification::where('user_id', auth()->id());
        $since = $request->query('since');
        $newNotifications = [];
        if ($since) {
            $newNotifications = (clone $notificationQuery)
                ->where('created_at', '>', $since)
                ->orderBy('created_at', 'desc')
                ->get(['id', 'type', 'data', 'title', 'message', 'link', 'created_at'])
                ->map(fn($n) => [
                    'id'              => $n->id,
                    'type'            => $n->type,
                    'title'           => $n->title,
                    'message'         => $n->message,
                    'created_at_diff' => $n->created_at->diffForHumans(),
                    'link'            => $n->link,
                    'has_appointment' => isset(($n->data ?? [])['appointment_id']),
                ]);
        }
        $unreadCount = (clone $notificationQuery)->unread()->count();

        return response()->json(array_merge($boardData, [
            'notifications' => [
                'count' => $unreadCount,
                'new'   => $newNotifications,
            ],
        ]));
    }
}
