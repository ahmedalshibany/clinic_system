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
        Schema::create('patient_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->string('filename');
            $table->string('original_name');
            $table->string('file_path');
            $table->string('file_type', 20); // pdf, jpg, png, etc.
            $table->unsignedInteger('file_size'); // bytes
            $table->enum('category', ['lab_result', 'xray', 'mri', 'prescription', 'report', 'other'])->default('other');
            $table->text('description')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['patient_id', 'category']);
            $table->index('uploaded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_files');
    }
};
