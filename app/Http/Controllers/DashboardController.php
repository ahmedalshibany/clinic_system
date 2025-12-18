<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        // Get stats
        $totalPatients = Patient::count();
        $totalDoctors = Doctor::count();
        $totalAppointments = Appointment::count();
        $todayAppointments = Appointment::whereDate('date', today())->count();

        // Status breakdown
        $pending = Appointment::where('status', 'pending')->count();
        $confirmed = Appointment::where('status', 'confirmed')->count();
        $completed = Appointment::where('status', 'completed')->count();
        $cancelled = Appointment::where('status', 'cancelled')->count();

        // Recent appointments
        $recentAppointments = Appointment::with(['patient', 'doctor'])
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->limit(5)
            ->get();

        // Weekly trend data
        $weeklyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $weeklyData[] = [
                'day' => $date->format('D'),
                'count' => Appointment::whereDate('date', $date)->count()
            ];
        }

        return view('dashboard', compact(
            'totalPatients',
            'totalDoctors',
            'totalAppointments',
            'todayAppointments',
            'pending',
            'confirmed',
            'completed',
            'cancelled',
            'recentAppointments',
            'weeklyData'
        ));
    }
}
