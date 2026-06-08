<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('appointments_doctor_date_time_unique');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['invoice_number']);
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (DB::connection()->getDriverName() === 'sqlite') {
                DB::statement(
                    'CREATE UNIQUE INDEX appointments_doctor_date_time_unique '
                    . 'ON appointments(doctor_id, date, time) '
                    . "WHERE status != 'cancelled'"
                );
            } else {
                $table->index(['doctor_id', 'date', 'time'], 'appointments_doctor_date_time_unique');
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->unique('invoice_number');
        });
    }
};
