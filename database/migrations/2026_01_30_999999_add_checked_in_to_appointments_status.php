<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'checked_in' to the status enum (MySQL only)
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('pending', 'scheduled', 'confirmed', 'waiting', 'in_progress', 'completed', 'cancelled', 'no_show', 'checked_in') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        // Revert to original enum (MySQL only — WARNING: might fail if 'checked_in' values exist)
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('pending', 'scheduled', 'confirmed', 'waiting', 'in_progress', 'completed', 'cancelled', 'no_show') NOT NULL DEFAULT 'pending'");
        }
    }
};
