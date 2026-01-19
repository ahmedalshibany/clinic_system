<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Common Stats
        $totalPatients = Patient::count();
        $totalDoctors = Doctor::count();
        
        // Initialize variables
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

        if ($user->role === 'doctor') {
            // DOCTOR DASHBOARD
            $doctor = Doctor::where('email', $user->email)->first();
            
            if ($doctor) {
                // Appointments
                $apptQuery = Appointment::where('doctor_id', $doctor->id);
                $totalAppointments = $apptQuery->count();
                $todayAppointments = $apptQuery->clone()->whereDate('date', today())->count();
                $waitingPatients = $apptQuery->clone()->whereDate('date', today())->where('status', 'waiting')->count();
                $weekAppointments = $apptQuery->clone()->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])->count();
                $monthAppointments = $apptQuery->clone()->whereMonth('date', now()->month)->count();

                // Status breakdown
                $pending = $apptQuery->clone()->where('status', 'pending')->count();
                $confirmed = $apptQuery->clone()->where('status', 'confirmed')->count();
                $completed = $apptQuery->clone()->where('status', 'completed')->count();
                $cancelled = $apptQuery->clone()->where('status', 'cancelled')->count();

                // Recent
                $recentAppointments = $apptQuery->clone()
                    ->with(['patient'])
                    ->orderBy('date', 'desc')
                    ->orderBy('time', 'desc')
                    ->limit(5)
                    ->get();
                
                // Weekly Data (Filtered)
                for ($i = 6; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $weeklyData[] = [
                        'day' => $date->format('D'),
                        'count' => $apptQuery->clone()->whereDate('date', $date)->count()
                    ];
                }
            }
        } else {
            // ADMIN DASHBOARD (Default)
            $totalAppointments = Appointment::count();
            $todayAppointments = Appointment::whereDate('date', today())->count();

            // Financials
            $todayRevenue = Payment::whereDate('payment_date', today())->sum('amount');
            $newPatientsMonth = Patient::whereMonth('created_at', now()->month)->count();
            
            $pendingInvQuery = Invoice::where('status', '!=', 'paid')->where('status', '!=', 'cancelled');
            $pendingInvoicesCount = $pendingInvQuery->count();
            // Calculate pending amount: total - amount_paid
            // Or roughly sum total for now if amount_paid logic is complex directly in DB, 
            // but we can do a raw select or just sum total - sum amount_paid?
            // Actually Invoice model has amount_paid and total columns.
            $pdInvoices = $pendingInvQuery->get();
            $pendingInvoicesAmount = $pdInvoices->sum('total') - $pdInvoices->sum('amount_paid');

            // Status breakdown
            $pending = Appointment::where('status', 'pending')->count();
            $confirmed = Appointment::where('status', 'confirmed')->count();
            $completed = Appointment::where('status', 'completed')->count();
            $cancelled = Appointment::where('status', 'cancelled')->count();

            // Recent
            $recentAppointments = Appointment::with(['patient', 'doctor'])
                ->orderBy('date', 'desc')
                ->orderBy('time', 'desc')
                ->limit(5)
                ->get();

             // Weekly Trend 
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $weeklyData[] = [
                    'day' => $date->format('D'),
                    'count' => Appointment::whereDate('date', $date)->count()
                ];
            }
        }

        return view('dashboard', compact(
            'user',
            'totalPatients',
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
            'monthAppointments'
        ));
    }
}
