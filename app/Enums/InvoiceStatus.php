<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Partial = 'partial';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Cancelled = 'cancelled';

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Sent, self::Cancelled],
            self::Sent => [self::Partial, self::Paid, self::Overdue, self::Cancelled],
            self::Partial => [self::Paid, self::Overdue, self::Cancelled],
            self::Paid => [],
            self::Overdue => [self::Partial, self::Paid],
            self::Cancelled => [],
        };
    }

    public function isFinalized(): bool
    {
        return in_array($this, [self::Paid, self::Cancelled], true);
    }

    public function allowsPayments(): bool
    {
        return in_array($this, [self::Sent, self::Partial, self::Overdue], true);
    }
}
