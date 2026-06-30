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
            $today = today();
            $tomorrow = today()->addDay();
            return view('nurse.dashboard', [
                'triageQueue' => Appointment::with(['patient:id,name', 'doctor:id,name'])
                    ->where('date', '>=', $today)
                    ->where('date', '<', $tomorrow)
                    ->whereIn('status', ['paid', 'checked_in'])
                    ->orderBy('time')
                    ->get(['id', 'patient_id', 'doctor_id', 'time', 'status']),
                'waitingList' => Appointment::with(['patient:id,name', 'doctor:id,name'])
                    ->where('date', '>=', $today)
                    ->where('date', '<', $tomorrow)
                    ->where('status', 'waiting')
                    ->orderBy('time')
                    ->get(['id', 'patient_id', 'doctor_id', 'time', 'status']),
            ]);
        }

        if ($user->hasRole('receptionist')) {
            $triageBoard = Appointment::with([
                    'patient:id,name,patient_code,phone',
                    'doctor:id,name,consultation_fee',
                ])
                ->whereDate('date', today())
                ->whereIn('status', ['pending', 'paid'])
                ->orderBy('time')
                ->get(['id', 'patient_id', 'doctor_id', 'time', 'type', 'status', 'fee']);

            $flowMonitor = Appointment::whereDate('date', today())
                ->selectRaw("
                    SUM(status = 'paid') AS paid,
                    SUM(status = 'checked_in') AS checked_in,
                    SUM(status = 'waiting') AS waiting,
                    SUM(status = 'in_progress') AS in_progress,
                    SUM(status = 'completed') AS completed,
                    SUM(status = 'cancelled') AS cancelled,
                    SUM(status = 'no_show') AS no_show
                ")->first()->toArray();

            $livePatients = Appointment::with(['patient:id,name,patient_code,phone', 'doctor:id,name'])
                ->whereDate('date', today())
                ->whereIn('status', [
                    'checked_in',
                    'waiting',
                    'in_progress',
                ])
                ->orderBy('time')
                ->get(['id', 'patient_id', 'doctor_id', 'time', 'status', 'checked_in_at', 'started_at']);

            return view('receptionist.dashboard', compact('triageBoard', 'flowMonitor', 'livePatients'));
        }

        if ($user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->firstOrFail();

            $triageQueue = Appointment::with([
                    'patient:id,name,patient_code',
                    'vital:appointment_id,temperature,bp_systolic,bp_diastolic,pulse,weight,height',
                ])
                ->where('doctor_id', $doctor->id)
                ->whereDate('date', today())
                ->whereIn('status', ['paid', 'checked_in'])
                ->orderBy('time')
                ->get(['id', 'patient_id', 'doctor_id', 'time', 'status', 'checked_in_at']);

            $readyQueue = Appointment::with([
                    'patient:id,name,patient_code',
                    'vital:appointment_id,temperature,bp_systolic,bp_diastolic,pulse,weight,height',
                ])
                ->where('doctor_id', $doctor->id)
                ->whereDate('date', today())
                ->where('status', 'waiting')
                ->orderBy('time')
                ->get(['id', 'patient_id', 'doctor_id', 'time', 'status', 'checked_in_at']);

            $activeSession = Appointment::with([
                    'patient:id,name,patient_code',
                    'vital:appointment_id,temperature,bp_systolic,bp_diastolic,pulse,weight,height',
                ])
                ->where('doctor_id', $doctor->id)
                ->whereDate('date', today())
                ->where('status', 'in_progress')
                ->first(['id', 'patient_id', 'doctor_id', 'time', 'status', 'checked_in_at', 'started_at', 'diagnosis', 'notes']);

            $todayAll = Appointment::where('doctor_id', $doctor->id)->whereDate('date', today());
            $stats = [
                'total'        => (clone $todayAll)->count(),
                'completed'    => (clone $todayAll)->where('status', 'completed')->count(),
                'in_progress'  => $activeSession ? 1 : 0,
                'triage'       => $triageQueue->count(),
                'waiting'      => $readyQueue->count(),
            ];

            return view('doctor.dashboard', compact('doctor', 'triageQueue', 'readyQueue', 'activeSession', 'stats'));
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
