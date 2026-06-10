<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Payment;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected AnalyticsService $analytics;

    public function __construct(AnalyticsService $analytics)
    {
        $this->analytics = $analytics;
    }

    public function index()
    {
        $user = Auth::user();

        $totalActivePatients = 0;
        $totalDoctors = Doctor::count();
        $totalAppointments = 0;
        $todayAppointments = 0;
        $pending = 0;
        $confirmed = 0;
        $completed = 0;
        $cancelled = 0;
        $todayRevenue = 0;
        $newPatientsMonth = 0;
        $pendingInvoicesCount = 0;
        $pendingInvoicesAmount = 0;
        $waitingPatients = 0;
        $weekAppointments = 0;
        $monthAppointments = 0;
        $recentAppointments = collect([]);
        $weeklyData = [];
        $readyToBillCount = 0;
        $triageQueue = collect([]);
        $waitingList = collect([]);

        // Shared analytics for charts
        $statusBreakdown = $this->analytics->getStatusBreakdown();
        $monthlyRevenue = $this->analytics->getMonthlyRevenue();
        $weeklyData = $this->analytics->getWeeklyTrend();
        $coreStats = $this->analytics->getCoreStats();

        $totalActivePatients = $coreStats['total_active_patients'];

        if ($user->hasRole('receptionist')) {
            $readyToBillCount = Appointment::where('status', 'completed')
                ->doesntHave('invoice')
                ->count();
        }

        if ($user->hasRole('nurse')) {
            $triageQueue = Appointment::with(['patient', 'doctor'])
                ->whereDate('date', today())
                ->whereIn('status', ['confirmed', 'checked_in'])
                ->orderBy('time')
                ->get();

            $waitingList = Appointment::with(['patient', 'doctor'])
                ->whereDate('date', today())
                ->where('status', 'waiting')
                ->orderBy('time')
                ->get();
        } elseif ($user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->first();

            if ($doctor) {
                $counts = $this->analytics->getAppointmentCountsForDoctor($doctor->id);
                $totalAppointments = $counts['total'];
                $todayAppointments = $counts['today'];
                $waitingPatients = $counts['waiting'];
                $weekAppointments = $counts['week'];
                $monthAppointments = $counts['month'];
                $pending = $counts['pending'];
                $confirmed = $counts['confirmed'];
                $completed = $counts['completed'];
                $cancelled = $counts['cancelled'];

                $recentAppointments = Appointment::where('doctor_id', $doctor->id)
                    ->with(['patient', 'doctor'])
                    ->orderBy('date', 'desc')
                    ->orderBy('time', 'desc')
                    ->limit(5)
                    ->get();
            }
        } else {
            $totalAppointments = Appointment::count();
            $todayAppointments = $coreStats['appointments_today'];
            $todayRevenue = Payment::whereDate('payment_date', today())->sum('amount');
            $newPatientsMonth = Patient::whereMonth('created_at', now()->month)->count();

            $pendingSummary = $this->analytics->getPendingInvoicesSummary();
            $pendingInvoicesCount = $pendingSummary['count'];
            $pendingInvoicesAmount = $pendingSummary['total_balance'];

            $pending = Appointment::where('status', 'pending')->count();
            $confirmed = Appointment::where('status', 'confirmed')->count();
            $completed = Appointment::where('status', 'completed')->count();
            $cancelled = Appointment::where('status', 'cancelled')->count();

            $recentAppointments = Appointment::with(['patient', 'doctor'])
                ->orderBy('date', 'desc')
                ->orderBy('time', 'desc')
                ->limit(5)
                ->get();
        }

        return view('dashboard', compact(
            'user',
            'totalActivePatients',
            'totalDoctors',
            'totalAppointments',
            'todayAppointments',
            'pending',
            'confirmed',
            'completed',
            'cancelled',
            'recentAppointments',
            'weeklyData',
            'todayRevenue',
            'newPatientsMonth',
            'pendingInvoicesCount',
            'pendingInvoicesAmount',
            'waitingPatients',
            'weekAppointments',
            'monthAppointments',
            'triageQueue',
            'waitingList',
            'readyToBillCount',
            'statusBreakdown',
            'monthlyRevenue',
            'coreStats'
        ));
    }
}
