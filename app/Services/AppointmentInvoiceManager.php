<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\Setting;
use Exception;
use Illuminate\Support\Facades\DB;

class AppointmentInvoiceManager
{
    public function generateConsultationInvoice(Appointment $appointment): ?Invoice
    {
        if ($appointment->invoice) {
            return $appointment->invoice;
        }

        $consultationService = Service::where('code', 'CONSULT-GEN')->first();

        if (!$consultationService) {
            throw new Exception('Consultation service (CONSULT-GEN) not found. Run the ServiceSeeder first.');
        }

        $fee = $appointment->fee ?? $appointment->doctor?->consultation_fee ?? 0;

        return DB::transaction(function () use ($appointment, $consultationService, $fee) {
            $dueDate = now()->addDays((int) Setting::get('default_due_days', 0))->toDateString();

            $invoice = Invoice::create([
                'patient_id' => $appointment->patient_id,
                'appointment_id' => $appointment->id,
                'created_by' => $appointment->doctor?->user_id,
                'subtotal' => $fee,
                'discount_percent' => 0,
                'discount_amount' => 0,
                'tax_percent' => 0,
                'tax_amount' => 0,
                'total' => $fee,
                'amount_paid' => 0,
                'status' => 'draft',
                'due_date' => $dueDate,
            ]);

            $invoice->items()->create([
                'service_id' => $consultationService->id,
                'description' => 'Consultation - ' . $appointment->type,
                'quantity' => 1,
                'unit_price' => $fee,
                'discount' => 0,
                'total' => $fee,
            ]);

            return $invoice;
        });
    }
}
