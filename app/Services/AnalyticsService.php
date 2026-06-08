<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function getCoreStats(): array
    {
        return [
            'total_active_patients' => Patient::active()->count(),
            'appointments_today' => Appointment::whereDate('date', today())->count(),
            'pending_vitals_queue' => Appointment::whereDate('date', today())
                ->whereIn('status', ['confirmed', 'checked_in'])
                ->whereDoesntHave('vital')
                ->count(),
            'revenue_this_month' => Payment::whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->sum('amount'),
        ];
    }

    public function getMonthlyRevenue(int $months = 6): array
    {
        $start = now()->subMonthsNoOverflow($months - 1)->startOfMonth();
        $end = now()->endOfMonth();

        $driver = DB::connection()->getDriverName();
        $yearExpr = $driver === 'sqlite' ? "strftime('%Y', payment_date)" : 'YEAR(payment_date)';
        $monthExpr = $driver === 'sqlite' ? "strftime('%m', payment_date)" : 'MONTH(payment_date)';

        $rows = Payment::whereBetween('payment_date', [$start, $end])
            ->selectRaw("{$yearExpr} as year, {$monthExpr} as month, SUM(amount) as revenue")
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->keyBy(fn($r) => $r->year . '-' . str_pad($r->month, 2, '0', STR_PAD_LEFT));

        $data = ['labels' => [], 'revenue' => []];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonthsNoOverflow($i);
            $key = $date->format('Y-m');
            $data['labels'][] = $date->format('M Y');
            $data['revenue'][] = round((float) ($rows[$key]->revenue ?? 0), 2);
        }
        return $data;
    }

    public function getStatusBreakdown(): array
    {
        return Appointment::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
    }

    public function getWeeklyTrend(): array
    {
        $start = now()->subDays(6)->startOfDay();
        $end = now()->endOfDay();

        $rows = Appointment::whereBetween('date', [$start, $end])
            ->selectRaw('DATE(date) as day, COUNT(*) as count')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $key = $date->format('Y-m-d');
            $data[] = [
                'day' => $date->format('D'),
                'count' => (int) ($rows[$key]->count ?? 0),
            ];
        }
        return $data;
    }

    public function getPendingInvoicesSummary(): array
    {
        $result = Invoice::whereNotIn('status', ['paid', 'cancelled'])
            ->selectRaw('count(*) as count, sum(total - amount_paid) as total_balance')
            ->first();

        return [
            'count' => (int) ($result->count ?? 0),
            'total_balance' => round((float) ($result->total_balance ?? 0), 2),
        ];
    }

    public function getAppointmentCountsForDoctor(int $doctorId): array
    {
        $query = Appointment::where('doctor_id', $doctorId);
        return [
            'total' => $query->count(),
            'today' => (clone $query)->whereDate('date', today())->count(),
            'waiting' => (clone $query)->whereDate('date', today())->where('status', 'waiting')->count(),
            'week' => (clone $query)->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'month' => (clone $query)->whereMonth('date', now()->month)->count(),
        ];
    }
}
