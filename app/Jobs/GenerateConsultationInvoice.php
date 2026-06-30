<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Services\AppointmentInvoiceManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateConsultationInvoice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;
    public int $backoff = 5;

    public function __construct(
        public Appointment $appointment
    ) {}

    public function handle(AppointmentInvoiceManager $manager): void
    {
        $manager->generateConsultationInvoice($this->appointment);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('Failed to generate consultation invoice', [
            'appointment_id' => $this->appointment->id,
            'patient_id' => $this->appointment->patient_id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
    }
}
