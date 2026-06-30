<?php

namespace App\Listeners;

use App\Events\PaymentCreated;

class RecalculateInvoiceStatus
{
    public function handle(PaymentCreated $event): void
    {
    }
}
