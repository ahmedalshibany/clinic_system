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

        if ($user->hasRole('nurse')) {
            return view('nurse.dashboard', [
                'triageQueue' => Appointment::with(['patient', 'doctor'])
                    ->whereDate('date', today())
                    ->whereIn('status', ['confirmed', 'checked_in'])
                    ->orderBy('time')
                    ->get(),
                'waitingList' => Appointment::with(['patient', 'doctor'])
                    ->whereDate('date', today())
                    ->where('status', 'waiting')
                    ->orderBy('time')
                    ->get(),
            ]);
        }

        if ($user->hasRole('receptionist')) {
            $triageBoard = Appointment::with(['patient:id,name,patient_code,phone', 'doctor:id,name'])
                ->whereDate('date', today())
                ->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED, Appointment::STATUS_SCHEDULED])
                ->orderBy('time')
                ->get();

            $allToday = Appointment::whereDate('date', today())->get();

            $flowMonitor = [
                'checked_in'  => $allToday->where('status', Appointment::STATUS_CHECKED_IN)->count(),
                'waiting'     => $allToday->where('status', Appointment::STATUS_WAITING)->count(),
                'in_progress' => $allToday->where('status', Appointment::STATUS_IN_PROGRESS)->count(),
                'completed'   => $allToday->where('status', Appointment::STATUS_COMPLETED)->count(),
                'cancelled'   => $allToday->where('status', Appointment::STATUS_CANCELLED)->count(),
                'no_show'     => $allToday->where('status', Appointment::STATUS_NO_SHOW)->count(),
            ];

            $livePatients = Appointment::with(['patient:id,name,patient_code,phone', 'doctor:id,name'])
                ->whereDate('date', today())
                ->whereIn('status', [
                    Appointment::STATUS_CHECKED_IN,
                    Appointment::STATUS_WAITING,
                    Appointment::STATUS_IN_PROGRESS,
                ])
                ->orderBy('time')
                ->get();

            return view('receptionist.dashboard', compact('triageBoard', 'flowMonitor', 'livePatients'));
        }

        if ($user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->firstOrFail();

            $waitingQueue = Appointment::with(['patient', 'vital'])
                ->where('doctor_id', $doctor->id)
                ->whereDate('date', today())
                ->where('status', Appointment::STATUS_WAITING)
                ->orderBy('time')
                ->get();

            $activeSession = Appointment::with(['patient', 'vital'])
                ->where('doctor_id', $doctor->id)
                ->whereDate('date', today())
                ->where('status', Appointment::STATUS_IN_PROGRESS)
                ->first();

            $todayAll = Appointment::where('doctor_id', $doctor->id)->whereDate('date', today());
            $stats = [
                'total'       => (clone $todayAll)->count(),
                'completed'   => (clone $todayAll)->where('status', Appointment::STATUS_COMPLETED)->count(),
                'in_progress' => $activeSession ? 1 : 0,
                'waiting'     => $waitingQueue->count(),
            ];

            return view('doctor.dashboard', compact('doctor', 'waitingQueue', 'activeSession', 'stats'));
        }

        // Admin dashboard
        $statusBreakdown = $this->analytics->getStatusBreakdown();
        $monthlyRevenue = $this->analytics->getMonthlyRevenue();
        $coreStats = $this->analytics->getCoreStats();

        $totalActivePatients = $coreStats['total_active_patients'];
        $todayAppointments = $coreStats['appointments_today'];
        $todayRevenue = Payment::whereDate('payment_date', today())->sum('amount');
        $newPatientsMonth = Patient::whereMonth('created_at', now()->month)->count();

        $pendingSummary = $this->analytics->getPendingInvoicesSummary();
        $pendingInvoicesCount = $pendingSummary['count'];
        $pendingInvoicesAmount = $pendingSummary['total_balance'];

        $totalAppointments = Appointment::count();
        $pending = Appointment::where('status', 'pending')->count();
        $confirmed = Appointment::where('status', 'confirmed')->count();
        $completed = Appointment::where('status', 'completed')->count();
        $cancelled = Appointment::where('status', 'cancelled')->count();

        $recentAppointments = Appointment::with(['patient', 'doctor'])
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'totalActivePatients', 'todayAppointments', 'todayRevenue',
            'newPatientsMonth', 'pendingInvoicesCount', 'pendingInvoicesAmount',
            'totalAppointments', 'pending', 'confirmed', 'completed', 'cancelled',
            'recentAppointments', 'statusBreakdown', 'monthlyRevenue'
        ));
    }
}
