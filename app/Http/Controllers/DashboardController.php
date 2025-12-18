<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $filter = $request->get('filter', 'all');
        
        // Base counts
        $totalPatients = Patient::count();
        $totalDoctors = Doctor::count();
        
        // Appointments query based on filter
        $appointmentQuery = Appointment::query();
        
        if ($filter === 'today') {
            $appointmentQuery->today();
        } elseif ($filter === 'week') {
            $appointmentQuery->thisWeek();
        }

        $totalAppointments = (clone $appointmentQuery)->count();
        $todayAppointments = Appointment::today()->count();

        // Status breakdown
        $pending = (clone $appointmentQuery)->pending()->count();
        $confirmed = (clone $appointmentQuery)->confirmed()->count();
        $completed = (clone $appointmentQuery)->completed()->count();
        $cancelled = (clone $appointmentQuery)->cancelled()->count();

        return response()->json([
            'success' => true,
            'data' => [
                'totalPatients' => $totalPatients,
                'totalDoctors' => $totalDoctors,
                'totalAppointments' => $totalAppointments,
                'todayAppointments' => $todayAppointments,
                'pending' => $pending,
                'confirmed' => $confirmed,
                'completed' => $completed,
                'cancelled' => $cancelled,
            ]
        ]);
    }

    /**
     * Get weekly trend data for chart.
     */
    public function weeklyTrend(): JsonResponse
    {
        $data = [];
        $labels = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = Appointment::where('date', $date->toDateString())->count();
            $data[] = $count;
            $labels[] = $date->format('D');
        }

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $labels,
                'data' => $data
            ]
        ]);
    }

    /**
     * Get recent appointments for dashboard feed.
     */
    public function recentAppointments(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 5);
        
        $appointments = Appointment::with(['patient:id,name', 'doctor:id,name,specialty'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($apt) {
                return [
                    'id' => $apt->id,
                    'patientName' => $apt->patient->name ?? 'Unknown',
                    'doctorName' => $apt->doctor->name ?? 'Unknown',
                    'specialty' => $apt->doctor->specialty ?? '',
                    'date' => $apt->date->format('M d, Y'),
                    'time' => $apt->time ? $apt->time->format('h:i A') : '',
                    'status' => $apt->status,
                    'type' => $apt->type,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $appointments
        ]);
    }

    /**
     * Get status distribution for pie chart.
     */
    public function statusDistribution(Request $request): JsonResponse
    {
        $filter = $request->get('filter', 'all');
        
        $query = Appointment::query();
        
        if ($filter === 'today') {
            $query->today();
        } elseif ($filter === 'week') {
            $query->thisWeek();
        }

        $distribution = [
            'pending' => (clone $query)->pending()->count(),
            'confirmed' => (clone $query)->confirmed()->count(),
            'completed' => (clone $query)->completed()->count(),
            'cancelled' => (clone $query)->cancelled()->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $distribution
        ]);
    }
}
