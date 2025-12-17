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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('age');
            $table->enum('gender', ['male', 'female']);
            $table->string('phone', 20);
            $table->text('address')->nullable();
            $table->string('email')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->text('medical_history')->nullable();
            $table->text('allergies')->nullable();
            $table->string('blood_type', 5)->nullable();
            $table->string('emergency_contact')->nullable();
            $table->string('emergency_phone', 20)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
