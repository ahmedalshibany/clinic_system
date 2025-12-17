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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->date('date');
            $table->time('time');
            $table->enum('type', ['Consultation', 'Checkup', 'Follow-up', 'Emergency'])->default('Consultation');
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('prescription')->nullable();
            $table->decimal('fee', 10, 2)->nullable();
            $table->timestamps();

            // Indexes for common queries
            $table->index(['date', 'status']);
            $table->index(['doctor_id', 'date']);
            $table->index(['patient_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
