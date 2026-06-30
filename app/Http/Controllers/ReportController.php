<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Payment;
use App\Models\User;
use App\Models\Service;
use App\Models\Doctor;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Dashboard with quick stats.
     */
    public function index()
    {
        $stats = [
            'today_revenue' => Payment::whereDate('payment_date', today())->sum('amount'),
            'month_revenue' => Payment::whereMonth('payment_date', now()->month)->sum('amount'),
            'total_patients' => Patient::count(),
            'new_patients_month' => Patient::whereMonth('created_at', now()->month)->count(),
            'appointments_today' => Appointment::today()->count(),
            'outstanding_invoices' => 0,
            'outstanding_amount' => 0,
        ];

        return view('reports.index', compact('stats'));
    }

    /**
     * Revenue Report.
     */
    /**
     * Revenue Report.
     */
    public function revenue(Request $request)
    {
        // Date Filters with quick_filter support
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        if ($request->has('quick_filter')) {
            switch ($request->input('quick_filter')) {
                case 'today':
                    $dateFrom = now()->startOfDay()->toDateString();
                    $dateTo = now()->endOfDay()->toDateString();
                    break;
                case 'this_week':
                    $dateFrom = now()->startOfWeek()->toDateString();
                    $dateTo = now()->endOfWeek()->toDateString();
                    break;
                case 'this_month':
                    $dateFrom = now()->startOfMonth()->toDateString();
                    $dateTo = now()->endOfMonth()->toDateString();
                    break;
                case 'this_year':
                    $dateFrom = now()->startOfYear()->toDateString();
                    $dateTo = now()->endOfYear()->toDateString();
                    break;
            }
        } else {
            $dateFrom = $dateFrom ?? now()->startOfMonth()->toDateString();
            $dateTo = $dateTo ?? now()->endOfMonth()->toDateString();
        }

        // Base Query
        $baseQuery = Payment::query()
            ->whereDate('payment_date', '>=', $dateFrom)
            ->whereDate('payment_date', '<=', $dateTo);

        // 1. Total Revenue (DB Aggregation)
        $total_revenue = (clone $baseQuery)->sum('amount');

        // 2. Revenue by Method (DB Aggregation)
        $revenue_by_method = (clone $baseQuery)
            ->select('payment_method', DB::raw('sum(amount) as total'))
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

        // 3. Daily Revenue (DB Aggregation)
        $daily_stats = (clone $baseQuery)
            ->selectRaw('DATE(payment_date) as date, sum(amount) as total')
            ->groupBy('date')
            ->get()
            ->pluck('total', 'date'); // ['2023-01-01' => 100, ...]

        // 4. Payments List (Paginated + Eager Loading)
        // Optimization: Select only needed columns for the list
        $payments = (clone $baseQuery)
            ->with(['invoice.patient:id,name,patient_code', 'receiver:id,name'])
            ->orderBy('payment_date', 'desc')
            ->paginate(50)
            ->withQueryString();

        // 5. Invoices Query (Billed / Sales)
        $invQuery = Invoice::query()
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->where('status', '!=', 'cancelled');
            
        $invoices_stats = $invQuery->selectRaw('count(*) as count, sum(total) as total_amount, avg(total) as avg_amount, sum(total - amount_paid) as pending_amount')->first();
            
        // 6. Paid Percentage (Collected / Billed)
        $paid_percentage = $invoices_stats->total_amount > 0 
            ? ($total_revenue / $invoices_stats->total_amount) * 100 
            : 0;

        // 7. Process Chart Data
        $chart_labels = [];
        $chart_data = [];
        $period = \Carbon\CarbonPeriod::create($dateFrom, $dateTo);
        foreach ($period as $date) {
            $day = $date->format('Y-m-d');
            $chart_labels[] = $date->format('M d');
            // Use the DB aggregated data
            $chart_data[] = (float) ($daily_stats[$day] ?? 0);
        }

        // 8. Sales by Category (Top 5)
        // Using chunk() example for hypothetical large processing if we needed to export
        // For now, the existing query is efficient enough
        $revenue_by_category = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->leftJoin('services', 'invoice_items.service_id', '=', 'services.id')
            ->whereBetween('invoices.created_at', [$dateFrom, $dateTo])
            ->where('invoices.status', '!=', 'cancelled')
            ->selectRaw('COALESCE(services.category, "Other") as category, sum(invoice_items.total) as total')
            ->groupBy('category')
            ->get();

        $pending_amount = $invoices_stats->pending_amount;
        $total_invoices = $invoices_stats->count;

        if ($total_revenue <= 0 || $total_invoices <= 0) {
            $avg_invoice = 0.00;
        } else {
            $avg_invoice = $total_revenue / $total_invoices;
        }

        return view('reports.revenue', compact(
            'payments', 'total_revenue', 'revenue_by_method', 
            'invoices_stats', 'paid_percentage', 
            'chart_labels', 'chart_data', 'revenue_by_category',
            'dateFrom', 'dateTo', 'pending_amount', 'avg_invoice', 'total_invoices'
        ));
    }

    /**
     * Revenue by Doctor.
     */
    public function revenueByDoctor(Request $request)
    {
        // Date Filters with quick_filter support
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        if ($request->has('quick_filter')) {
            switch ($request->input('quick_filter')) {
                case 'today':
                    $dateFrom = now()->startOfDay()->toDateString();
                    $dateTo = now()->endOfDay()->toDateString();
                    break;
                case 'this_week':
                    $dateFrom = now()->startOfWeek()->toDateString();
                    $dateTo = now()->endOfWeek()->toDateString();
                    break;
                case 'this_month':
                    $dateFrom = now()->startOfMonth()->toDateString();
                    $dateTo = now()->endOfMonth()->toDateString();
                    break;
                case 'this_year':
                    $dateFrom = now()->startOfYear()->toDateString();
                    $dateTo = now()->endOfYear()->toDateString();
                    break;
            }
        } else {
            $dateFrom = $dateFrom ?? now()->startOfMonth()->toDateString();
            $dateTo = $dateTo ?? now()->endOfMonth()->toDateString();
        }

        $data = DB::table('payments')
            ->join('invoices', 'payments.invoice_id', '=', 'invoices.id')
            ->leftJoin('appointments', 'invoices.appointment_id', '=', 'appointments.id')
            ->leftJoin('doctors', 'appointments.doctor_id', '=', 'doctors.id')
            ->leftJoin('users', 'doctors.user_id', '=', 'users.id')
            ->whereBetween('payments.payment_date', [$dateFrom, $dateTo])
            ->select('doctors.id as doctor_id', 'users.name as user_name')
            ->selectRaw('SUM(payments.amount) as total_earned')
            ->selectRaw('COUNT(DISTINCT appointments.id) as appointment_count')
            ->groupBy('doctors.id', 'users.name')
            ->get()
            ->map(function ($row) {
                $row->doctor_name = $row->user_name ?? __('messages.unassigned');
                return $row;
            });

        $total_earned_sum   = $data->sum('total_earned');
        $total_appointments = $data->sum('appointment_count');

        $top_doctor_row = $data->sortByDesc('total_earned')->first();
        $top_doctor     = ($top_doctor_row && $top_doctor_row->doctor_name) ? $top_doctor_row->doctor_name : __('messages.unassigned');
        $doctor_count   = $data->whereNotNull('doctor_name')->count();

        return view('reports.revenue_doctor', compact(
            'data', 'dateFrom', 'dateTo',
            'total_earned_sum', 'total_appointments',
            'top_doctor', 'doctor_count'
        ));
    }

    /**
     * Revenue by Service.
     */
    public function revenueByService(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        // 1. Unified Quick Filter Handling using strict string dates
        if ($request->has('quick_filter')) {
            switch ($request->input('quick_filter')) {
                case 'today':
                    $dateFrom = now()->startOfDay()->toDateString();
                    $dateTo = now()->endOfDay()->toDateString();
                    break;
                case 'this_week':
                    $dateFrom = now()->startOfWeek()->toDateString();
                    $dateTo = now()->endOfWeek()->toDateString();
                    break;
                case 'this_month':
                    $dateFrom = now()->startOfMonth()->toDateString();
                    $dateTo = now()->endOfMonth()->toDateString();
                    break;
                case 'this_year':
                    $dateFrom = now()->startOfYear()->toDateString();
                    $dateTo = now()->endOfYear()->toDateString();
                    break;
            }
        } else {
            $dateFrom = $dateFrom ?? now()->startOfMonth()->toDateString();
            $dateTo = $dateTo ?? now()->endOfMonth()->toDateString();
        }

        // 2. Base Query Formulation (Billed Sales via Invoice Items)
        $query = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->join('services', 'invoice_items.service_id', '=', 'services.id')
            ->whereBetween(DB::raw('DATE(invoices.created_at)'), [$dateFrom, $dateTo])
            ->where('invoices.status', '!=', 'cancelled')
            ->select(
                'services.name as service_name',
                DB::raw('SUM(invoice_items.quantity) as total_qty'),
                DB::raw('SUM(invoice_items.total) as total_sales')
            )
            ->groupBy('services.id', 'services.name');

        $data = $query->get();

        // 3. Compute High-End Summary Aggregates for the Symmetrical Cards
        $total_sales_sum   = $data->sum('total_sales');
        $total_items_count = $data->sum('total_qty');

        $top_service_row   = $data->sortByDesc('total_sales')->first();
        $top_service       = ($top_service_row && $top_service_row->service_name) ? $top_service_row->service_name : '—';
        $service_count     = $data->count();

        // Explicitly pull currency symbol for controller architectural hygiene
        $currencySymbol = Setting::get('currency_symbol', '$');

        return view('reports.revenue_service', compact(
            'data', 'dateFrom', 'dateTo',
            'total_sales_sum', 'total_items_count',
            'top_service', 'service_count', 'currencySymbol'
        ));
    }

    /**
     * Patients Report.
     */
    public function patients(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        // 1. Unified Quick Filter Handling using strict string dates
        if ($request->has('quick_filter')) {
            switch ($request->input('quick_filter')) {
                case 'today':
                    $dateFrom = now()->startOfDay()->toDateString();
                    $dateTo = now()->endOfDay()->toDateString();
                    break;
                case 'this_week':
                    $dateFrom = now()->startOfWeek()->toDateString();
                    $dateTo = now()->endOfWeek()->toDateString();
                    break;
                case 'this_month':
                    $dateFrom = now()->startOfMonth()->toDateString();
                    $dateTo = now()->endOfMonth()->toDateString();
                    break;
                case 'this_year':
                    $dateFrom = now()->startOfYear()->toDateString();
                    $dateTo = now()->endOfDay()->toDateString();
                    break;
            }
        }

        // 2. Base Query Setup for Active Registers
        $baseQuery = Patient::query();
        if ($dateFrom && $dateTo) {
            $baseQuery->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo]);
        }

        $allFilteredPatients = (clone $baseQuery)->get();

        // 3. Dynamic Age Bucket Calculation using SQL-Parity Birthday Diffs
        // Child: < 18, Senior: > 60, Adult: 18-60
        $ageGroupsQuery = Patient::query()
            ->select(DB::raw("
                CASE 
                    WHEN date_of_birth IS NULL THEN 'Unknown'
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURRENT_DATE) < 18 THEN 'Child'
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURRENT_DATE) > 60 THEN 'Senior'
                    ELSE 'Adult'
                END as age_group
            "), DB::raw('COUNT(*) as total'))
            ->whereIn('id', $allFilteredPatients->pluck('id')->toArray() ?: [0])
            ->groupBy('age_group')
            ->get();

        $age_groups = $ageGroupsQuery->pluck('total', 'age_group')->toArray();

        // 4. Gender Distribution Metrics
        $genderQuery = Patient::query()
            ->select('gender', DB::raw('COUNT(*) as total'))
            ->whereIn('id', $allFilteredPatients->pluck('id')->toArray() ?: [0])
            ->groupBy('gender')
            ->get();

        $gender_stats = $genderQuery->pluck('total', 'gender')->toArray();

        // 5. Geographic/Location Distribution Metrics (Dynamic Top 5 Cities/Areas)
        // Safely falls back to an unassigned label if the city field is empty or null
        $locationQuery = Patient::query()
            ->select(DB::raw("COALESCE(city, 'غير محدد') as location_name"), DB::raw('COUNT(*) as total'))
            ->whereIn('id', $allFilteredPatients->pluck('id')->toArray() ?: [0])
            ->groupBy('location_name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // 6. Top-Level Core Summary Counter Variables
        $total_patients_count = $allFilteredPatients->count();
        $total_male_count     = $genderQuery->where('gender', 'male')->first()->total ?? 0;
        $total_female_count   = $genderQuery->where('gender', 'female')->first()->total ?? 0;

        // Recent registrations collection block for listing section
        $patients = $baseQuery->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('reports.patients', compact(
            'patients', 'gender_stats', 'age_groups', 'locationQuery',
            'total_patients_count', 'total_male_count', 'total_female_count',
            'dateFrom', 'dateTo'
        ));
    }

    /**
     * Appointments Report.
     */
    public function appointments(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $doctorId = $request->input('doctor_id');
        $status = $request->input('status');

        // 1. Unified Quick Filter Handling using strict string dates
        // Default to current month when no filter or date range is specified
        if (!$request->has('quick_filter') && !$dateFrom && !$dateTo) {
            $request->merge(['quick_filter' => 'this_month']);
        }
        if ($request->has('quick_filter')) {
            switch ($request->input('quick_filter')) {
                case 'today':
                    $dateFrom = now()->startOfDay()->toDateString();
                    $dateTo = now()->endOfDay()->toDateString();
                    break;
                case 'this_week':
                    $dateFrom = now()->startOfWeek()->toDateString();
                    $dateTo = now()->endOfWeek()->toDateString();
                    break;
                case 'this_month':
                    $dateFrom = now()->startOfMonth()->toDateString();
                    $dateTo = now()->endOfMonth()->toDateString();
                    break;
                case 'this_year':
                    $dateFrom = now()->startOfYear()->toDateString();
                    $dateTo = now()->endOfDay()->toDateString();
                    break;
            }
        }

        // 2. Base Query Formulation with eager loading
        $baseQuery = \App\Models\Appointment::query()->with(['doctor.user', 'patient']);

        // Apply strict structural operational filters
        if ($dateFrom && $dateTo) {
            $baseQuery->whereBetween(\DB::raw('DATE(date)'), [$dateFrom, $dateTo]);
        }
        if ($doctorId) {
            $baseQuery->where('doctor_id', $doctorId);
        }
        if ($status) {
            // Normalize status check for database hyphen/underscore safety
            if ($status === 'no_show' || $status === 'no-show') {
                $baseQuery->whereIn('status', ['no-show', 'no_show']);
            } else {
                $baseQuery->where('status', $status);
            }
        }

        // Clone to compute solid mathematical aggregates before pagination variables binding
        $calcCollection = (clone $baseQuery)->get();

        // 3. Compute Top-Level Hardcoded KPI Scalars to lock the 4-column Frontend Grid
        $total_appointments_count = $calcCollection->count();
        $completed_count          = $calcCollection->whereIn('status', ['completed', 'تمت'])->count();
        $scheduled_count          = $calcCollection->whereIn('status', ['scheduled', 'confirmed', 'مجدول'])->count();
        $cancelled_and_noshow     = $calcCollection->whereIn('status', ['cancelled', 'no-show', 'no_show', 'ملغي', 'غائب'])->count();

        // 4. Fetch Active Doctors List using consistent display properties mapping
        $doctors = \App\Models\Doctor::with('user')->get();

        // 5. Paginate final ordered results block
        $appointments = $baseQuery->orderBy('date', 'desc')->paginate(10)->withQueryString();

        return view('reports.appointments', compact(
            'appointments', 'doctors', 'dateFrom', 'dateTo', 'doctorId', 'status',
            'total_appointments_count', 'completed_count', 'scheduled_count', 'cancelled_and_noshow'
        ));
    }

    /**
     * Outstanding Invoices Report.
     */
    public function outstanding(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        // 1. Unified Quick Filter Handling using clean string dates
        if ($request->has('quick_filter')) {
            switch ($request->input('quick_filter')) {
                case 'today':
                    $dateFrom = now()->startOfDay()->toDateString();
                    $dateTo = now()->endOfDay()->toDateString();
                    break;
                case 'this_week':
                    $dateFrom = now()->startOfWeek()->toDateString();
                    $dateTo = now()->endOfWeek()->toDateString();
                    break;
                case 'this_month':
                    $dateFrom = now()->startOfMonth()->toDateString();
                    $dateTo = now()->endOfMonth()->toDateString();
                    break;
                case 'this_year':
                    $dateFrom = now()->startOfYear()->toDateString();
                    $dateTo = now()->endOfDay()->toDateString();
                    break;
            }
        } else {
            $dateFrom = $dateFrom ?? now()->startOfMonth()->toDateString();
            $dateTo = $dateTo ?? now()->endOfMonth()->toDateString();
        }

        $baseQuery = Invoice::query()->with('patient')
            ->where('status', 'cancelled');

        if ($dateFrom && $dateTo) {
            $baseQuery->whereBetween(DB::raw('DATE(due_date)'), [$dateFrom, $dateTo]);
        }

        // 3. Compute Financial Summary Aggregates
        $calcCollection = (clone $baseQuery)->get();

        $total_outstanding = $calcCollection->sum(function ($inv) {
            return $inv->total - $inv->amount_paid;
        });

        $overdue_invoices_count = 0;
        $total_pending_bills = $calcCollection->count();

        $topDebtorGroup = $calcCollection->groupBy('patient_id')->map(function ($group) {
            return [
                'name'    => $group->first()->patient->name ?? __('messages.unassigned'),
                'balance' => $group->sum(function ($inv) {
                    return $inv->total - $inv->amount_paid;
                }),
            ];
        })->sortByDesc('balance')->first();

        $top_debtor_patient = $topDebtorGroup ? $topDebtorGroup['name'] : '—';

        // 4. Paginated List
        $invoices = $baseQuery->orderBy('due_date', 'asc')->paginate(50)->withQueryString();

        $currencySymbol = Setting::get('currency_symbol', '$');

        return view('reports.outstanding', compact(
            'invoices',
            'dateFrom',
            'dateTo',
            'total_outstanding',
            'overdue_invoices_count',
            'top_debtor_patient',
            'total_pending_bills',
            'currencySymbol'
        ));
    }

    /**
     * Export to Excel (CSV).
     */
    public function exportExcel($report, Request $request)
    {
        $fileName = $report . '_report_' . date('Y-m-d') . '.csv';
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        // Logic to fetch data based on $report type reuse previous methods logic
        // For brevity, I'll implement a basic switch here
        $callback = function() use ($report, $request) {
            $file = fopen('php://output', 'w');
            
            if ($report === 'revenue') {
                fputcsv($file, ['Date', 'Invoice #', 'Patient', 'Method', 'Amount']);
                $from = $request->date_from ?? now()->startOfMonth()->toDateString();
                $to   = $request->date_to   ?? now()->endOfMonth()->toDateString();
                $payments = Payment::with('invoice.patient')
                    ->whereDate('payment_date', '>=', $from)
                    ->whereDate('payment_date', '<=', $to)
                    ->get();
                foreach ($payments as $p) {
                    fputcsv($file, [
                        $p->payment_date->format('Y-m-d'),
                        $p->invoice->invoice_number,
                        $p->invoice->patient->name,
                        $p->payment_method,
                        $p->amount
                    ]);
                }
            }
            // Add other cases...
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export to PDF.
     */
    public function exportPdf($report, Request $request)
    {
        // Without dompdf, we can render a "print view" and trigger JS print, 
        // or return a view that says "Press Ctrl+P".
        // Usually, we redirect to the report view with 'print=true' query param
        // to load a print-optimized CSS.
        
        return redirect()->route("reports.{$report}", $request->all() + ['print' => true]);
    }
}
