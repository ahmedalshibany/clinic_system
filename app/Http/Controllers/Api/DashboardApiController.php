<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Payment;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardApiController extends Controller
{
    protected AnalyticsService $analytics;

    public function __construct(AnalyticsService $analytics)
    {
        $this->analytics = $analytics;
    }

    public function stats(Request $request): JsonResponse
    {
        $this->authorizeDashboardAccess();
        $user = Auth::user();
        $filter = $request->get('filter', 'all');

        $data = [
            'totalPatients' => Patient::count(),
            'totalDoctors' => Doctor::count(),
            'totalAppointments' => Appointment::count(),
            'todayAppointments' => Appointment::whereDate('date', today())->count(),
            'pending' => Appointment::where('status', 'pending')->count(),
            'confirmed' => Appointment::where('status', 'confirmed')->count(),
            'completed' => Appointment::where('status', 'completed')->count(),
            'cancelled' => Appointment::where('status', 'cancelled')->count(),
        ];

        if ($user->hasRole('doctor')) {
            $doctor = Doctor::where('user_id', $user->id)->first();
            if ($doctor) {
                $query = Appointment::where('doctor_id', $doctor->id);
                $data = [
                    'totalPatients' => Patient::count(),
                    'totalDoctors' => Doctor::count(),
                    'totalAppointments' => (clone $query)->count(),
                    'todayAppointments' => (clone $query)->whereDate('date', today())->count(),
                    'pending' => (clone $query)->where('status', 'pending')->count(),
                    'confirmed' => (clone $query)->where('status', 'confirmed')->count(),
                    'completed' => (clone $query)->where('status', 'completed')->count(),
                    'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
                ];
            }
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function weeklyTrend(): JsonResponse
    {
        $this->authorizeDashboardAccess();
        $trend = $this->analytics->getWeeklyTrend();
        $labels = array_map(fn($d) => $d['day'], $trend);
        $data = array_map(fn($d) => $d['count'], $trend);

        return response()->json([
            'success' => true,
            'data' => ['labels' => $labels, 'data' => $data]
        ]);
    }

    public function recentAppointments(Request $request): JsonResponse
    {
        $this->authorizeDashboardAccess();
        $limit = (int) $request->get('limit', 5);
        $user = Auth::user();

        $query = Appointment::with(['patient', 'doctor'])
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc');

        if ($user->hasRole('doctor')) {
            $doctor = Doctor::where('user_id', $user->id)->first();
            if ($doctor) {
                $query->where('doctor_id', $doctor->id);
            }
        }

        $appointments = $query->limit($limit)->get();

        $data = $appointments->map(fn($appt) => [
            'id' => $appt->id,
            'patientName' => $appt->patient?->name ?? __('messages.unknown'),
            'doctorName' => $appt->doctor?->name ?? __('messages.unknown'),
            'date' => \Carbon\Carbon::parse($appt->date)->format('M d'),
            'time' => \Carbon\Carbon::parse($appt->time)->format('H:i'),
            'status' => $appt->status,
        ]);

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function statusDistribution(Request $request): JsonResponse
    {
        $this->authorizeDashboardAccess();
        $breakdown = $this->analytics->getStatusBreakdown();

        return response()->json([
            'success' => true,
            'data' => [
                'pending' => $breakdown['pending'] ?? 0,
                'confirmed' => $breakdown['confirmed'] ?? 0,
                'completed' => $breakdown['completed'] ?? 0,
                'cancelled' => $breakdown['cancelled'] ?? 0,
            ]
        ]);
    }

    private function authorizeDashboardAccess(): void
    {
        $user = Auth::user();

        if (!$user || in_array($user->role, ['receptionist', 'nurse'])) {
            abort(403, __('messages.unauthorized'));
        }
    }
}
