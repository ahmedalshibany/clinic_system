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
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
            $table->date('visit_date');
            $table->text('chief_complaint');
            $table->json('vital_signs')->nullable(); // {bp_systolic, bp_diastolic, pulse, temp, weight, height, oxygen}
            $table->text('history_of_illness')->nullable();
            $table->text('physical_examination')->nullable();
            $table->text('diagnosis');
            $table->string('diagnosis_code')->nullable(); // ICD-10
            $table->text('treatment_plan')->nullable();
            $table->text('notes')->nullable(); // Private doctor notes
            $table->date('follow_up_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
