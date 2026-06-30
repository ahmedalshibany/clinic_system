<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public function isFinalized(): bool
    {
        return true;
    }

    public function allowsPayments(): bool
    {
        return $this === self::Cancelled;
    }
}
