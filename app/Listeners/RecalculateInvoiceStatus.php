<?php

namespace App\Listeners;

use App\Enums\InvoiceStatus;
use App\Events\PaymentCreated;
use Illuminate\Support\Facades\DB;

class RecalculateInvoiceStatus
{
    public function handle(PaymentCreated $event): void
    {
        $invoice = $event->payment->invoice;

        DB::transaction(function () use ($invoice) {
            $invoice = $invoice->newQuery()
                ->where('id', $invoice->id)
                ->lockForUpdate()
                ->firstOrFail();

            $totalPaid = $invoice->payments()->sum('amount');

            $invoice->amount_paid = $totalPaid;

            if ($totalPaid >= $invoice->total) {
                $invoice->status = InvoiceStatus::Paid;
            } elseif ($totalPaid > 0) {
                $invoice->status = InvoiceStatus::Partial;
            }

            $invoice->save();
        });
    }
}
