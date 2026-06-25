<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;

class CheckOverdueInvoices extends Command
{
    protected $signature = 'invoices:check-overdue';
    protected $description = 'Mark overdue invoices where due date has passed';

    public function handle(): void
    {
        $count = Invoice::whereIn('status', ['sent', 'partial'])
            ->whereDate('due_date', '<', now())
            ->whereColumn('amount_paid', '<', 'total')
            ->update(['status' => 'overdue']);

        $this->info("Marked {$count} invoice(s) as overdue.");
    }
}
