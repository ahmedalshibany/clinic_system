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
        // 1. Modify users table to add 'nurse' role
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'doctor', 'receptionist', 'nurse') NOT NULL DEFAULT 'receptionist'");

        // 2. Create vitals table
        Schema::create('vitals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users'); // User who recorded vitals
            
            // Vital Signs
            $table->decimal('temperature', 3, 1);
            $table->integer('bp_systolic')->unsigned();
            $table->integer('bp_diastolic')->unsigned();
            $table->integer('pulse')->unsigned();
            $table->integer('respiratory_rate')->unsigned()->nullable();
            $table->decimal('weight', 5, 2);
            $table->decimal('height', 5, 2)->nullable();
            $table->integer('oxygen_saturation')->nullable(); // SpO2 %
            
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vitals');

        // Revert users table role (Optional / Risky if nurses exist)
        // We generally don't remove enum values in down() to prevent data loss, 
        // but for strict reversibility:
        // DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'doctor', 'receptionist') NOT NULL DEFAULT 'receptionist'");
    }
};
