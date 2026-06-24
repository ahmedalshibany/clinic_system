<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Notification;
use App\Models\Vital;
use App\Services\VitalService;
use App\Http\Requests\Vitals\StoreVitalRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class NurseController extends Controller
{
    protected VitalService $vitalService;

    public function __construct(VitalService $vitalService)
    {
        $this->vitalService = $vitalService;
    }

    public function createVitals(Appointment $appointment)
    {
        $this->authorize('create', Vital::class);
        if (!in_array($appointment->status, [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED, Appointment::STATUS_CHECKED_IN, Appointment::STATUS_SCHEDULED]) && !$appointment->vitals_unlocked) {
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
     * AJAX: Save vitals from inline dashboard form.
     */
    public function storeVitalsAjax(StoreVitalRequest $request, Appointment $appointment)
    {
        $this->authorize('create', Vital::class);
        try {
            if (!in_array($appointment->status, [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED, Appointment::STATUS_CHECKED_IN, Appointment::STATUS_SCHEDULED]) && !$appointment->vitals_unlocked) {
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
     * AJAX: Return today's triage queue (confirmed/checked_in) + notification data as JSON.
     */
    public function triageQueueApi(Request $request)
    {
        $this->authorize('viewAny', Appointment::class);
        $today = today();
        $tomorrow = today()->addDay();

        $triageQueue = Appointment::with(['patient:id,name', 'doctor:id,name'])
            ->where('date', '>=', $today)
            ->where('date', '<', $tomorrow)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->orderBy('time')
            ->get(['id', 'patient_id', 'doctor_id', 'time', 'status']);

        $waitingList = Appointment::with(['patient:id,name', 'doctor:id,name'])
            ->where('date', '>=', $today)
            ->where('date', '<', $tomorrow)
            ->where('status', 'waiting')
            ->orderBy('time')
            ->get(['id', 'patient_id', 'doctor_id', 'time', 'status']);

        // Batch notification data into this endpoint to eliminate double-polling
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

        return response()->json([
            'triageQueue' => $triageQueue->map(fn($a) => [
                'id'            => $a->id,
                'time'          => $a->time->format('H:i'),
                'patient_name'  => $a->patient->name,
                'doctor_name'   => $a->doctor->name,
                'status'        => $a->status,
                'status_label'  => $a->status === 'checked_in' ? __('messages.checked_in') : __('Confirmed'),
            ]),
            'waitingList' => $waitingList->map(fn($a) => [
                'id'            => $a->id,
                'time'          => $a->time->format('H:i'),
                'patient_name'  => $a->patient->name,
                'doctor_name'   => $a->doctor->name,
                'status'        => $a->status,
            ]),
            'triageCount'  => $triageQueue->count(),
            'waitingCount' => $waitingList->count(),
            'notifications' => [
                'count' => $unreadCount,
                'new'   => $newNotifications,
            ],
        ]);
    }
}
