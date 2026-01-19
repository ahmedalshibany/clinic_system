<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->text('reason')->nullable()->after('status');
            $table->timestamp('checked_in_at')->nullable()->after('notes');
            $table->timestamp('started_at')->nullable()->after('checked_in_at');
            $table->timestamp('completed_at')->nullable()->after('started_at');
        });

        // Update status enum
        DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('scheduled', 'confirmed', 'waiting', 'in_progress', 'completed', 'cancelled', 'no_show', 'pending') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['reason', 'checked_in_at', 'started_at', 'completed_at']);
        });

        // Revert status enum matches original migration
        DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('pending', 'confirmed', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");
    }
};
