<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->index(['doctor_id', 'date', 'status'], 'appts_doctor_date_status_idx');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->index(['patient_id', 'status'], 'invoices_patient_status_idx');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index('invoice_id', 'payments_invoice_id_idx');
        });

        Schema::table('medical_records', function (Blueprint $table) {
            $table->index(['patient_id', 'visit_date'], 'medical_records_patient_visit_idx');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('appts_doctor_date_status_idx');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('invoices_patient_status_idx');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_invoice_id_idx');
        });

        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropIndex('medical_records_patient_visit_idx');
        });
    }
};
