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
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('specialty');
            $table->string('phone', 20);
            $table->string('email')->nullable();
            $table->text('bio')->nullable();
            $table->string('avatar')->nullable();
            $table->json('working_days')->nullable(); // e.g., ["Monday", "Tuesday", "Wednesday"]
            $table->time('work_start_time')->nullable();
            $table->time('work_end_time')->nullable();
            $table->decimal('consultation_fee', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
