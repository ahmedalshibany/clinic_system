<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->index(['doctor_id', 'date', 'status', 'time'], 'appointments_doctor_date_status_time_idx');
            $table->index(['date', 'status', 'doctor_id', 'time'], 'appointments_date_status_doctor_time_idx');
        });

        Schema::table('vitals', function (Blueprint $table) {
            $table->index(['appointment_id'], 'vitals_appointment_id_idx');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('appointments_doctor_date_status_time_idx');
            $table->dropIndex('appointments_date_status_doctor_time_idx');
        });

        Schema::table('vitals', function (Blueprint $table) {
            $table->dropIndex('vitals_appointment_id_idx');
        });
    }
};
