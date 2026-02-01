<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'checked_in' to the status enum
        DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('pending', 'scheduled', 'confirmed', 'waiting', 'in_progress', 'completed', 'cancelled', 'no_show', 'checked_in') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Revert to original enum (WARNING: this might fail if there are 'checked_in' values)
        DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('pending', 'scheduled', 'confirmed', 'waiting', 'in_progress', 'completed', 'cancelled', 'no_show') NOT NULL DEFAULT 'pending'");
    }
};
