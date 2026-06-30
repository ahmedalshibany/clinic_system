<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckOverdueInvoices extends Command
{
    protected $signature = 'invoices:check-overdue';
    protected $description = 'Command disabled — overdue status removed';

    public function handle(): void
    {
        $this->info('Overdue invoice check is disabled. Only paid/cancelled statuses are used.');
    }
}
