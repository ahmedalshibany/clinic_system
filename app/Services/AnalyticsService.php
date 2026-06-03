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
        $data = ['labels' => [], 'revenue' => []];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonthsNoOverflow($i);
            $revenue = Payment::whereYear('payment_date', $date->year)
                ->whereMonth('payment_date', $date->month)
                ->sum('amount');
            $data['labels'][] = $date->format('M Y');
            $data['revenue'][] = round((float) $revenue, 2);
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
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $data[] = [
                'day' => $date->format('D'),
                'count' => Appointment::whereDate('date', $date)->count(),
            ];
        }
        return $data;
    }

    public function getPendingInvoicesSummary(): array
    {
        $pending = Invoice::whereNotIn('status', ['paid', 'cancelled'])->get();
        return [
            'count' => $pending->count(),
            'total_balance' => round((float) $pending->sum(fn($inv) => $inv->total - $inv->amount_paid), 2),
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
