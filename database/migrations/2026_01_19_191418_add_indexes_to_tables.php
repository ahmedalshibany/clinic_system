<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->index(['patient_code', 'phone', 'status']);
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->index(['date', 'status', 'doctor_id', 'patient_id']);
            $table->index('status');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->index(['invoice_number', 'status', 'patient_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropIndex(['patient_code', 'phone', 'status']);
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['date', 'status', 'doctor_id', 'patient_id']);
            $table->dropIndex('status');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['invoice_number', 'status', 'patient_id']);
            $table->dropIndex('status');
        });
    }
};
