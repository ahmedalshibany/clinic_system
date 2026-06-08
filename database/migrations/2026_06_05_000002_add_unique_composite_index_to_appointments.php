<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove duplicates keeping the earliest created record
        $duplicates = DB::table('appointments')
            ->select('doctor_id', 'date', 'time')
            ->groupBy('doctor_id', 'date', 'time')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            $keepId = DB::table('appointments')
                ->where('doctor_id', $dup->doctor_id)
                ->where('date', $dup->date)
                ->where('time', $dup->time)
                ->orderBy('created_at')
                ->value('id');

            DB::table('appointments')
                ->where('doctor_id', $dup->doctor_id)
                ->where('date', $dup->date)
                ->where('time', $dup->time)
                ->where('id', '!=', $keepId)
                ->delete();
        }

        Schema::table('appointments', function (Blueprint $table) {
            if (DB::connection()->getDriverName() === 'sqlite') {
                // Partial unique index — allows rebooking cancelled slots
                DB::statement(
                    'CREATE UNIQUE INDEX appointments_doctor_date_time_unique '
                    . 'ON appointments(doctor_id, date, time) '
                    . "WHERE status != 'cancelled'"
                );
            } else {
                // MySQL does not support partial indexes on InnoDB;
                // use a regular composite index + app-level lockForUpdate
                $table->index(['doctor_id', 'date', 'time'], 'appointments_doctor_date_time_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('appointments_doctor_date_time_unique');
        });
    }
};
