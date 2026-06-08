<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->index('payment_date', 'payments_payment_date_idx');
            $table->index('payment_method', 'payments_payment_method_idx');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->index(['created_at', 'status'], 'invoices_created_at_status_idx');
            $table->index('due_date', 'invoices_due_date_idx');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->index('invoice_id', 'invoice_items_invoice_id_idx');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_payment_date_idx');
            $table->dropIndex('payments_payment_method_idx');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('invoices_created_at_status_idx');
            $table->dropIndex('invoices_due_date_idx');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropIndex('invoice_items_invoice_id_idx');
        });
    }
};
