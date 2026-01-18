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
        Schema::table('patients', function (Blueprint $table) {
            // Patient identification
            $table->string('patient_code', 20)->unique()->nullable()->after('id');
            $table->string('name_en')->nullable()->after('name');
            $table->string('nationality', 100)->nullable()->after('name_en');
            $table->string('id_number', 50)->nullable()->after('nationality');
            
            // Personal info
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable()->after('gender');
            $table->string('occupation', 100)->nullable()->after('marital_status');
            
            // Contact info
            $table->string('phone_secondary', 20)->nullable()->after('phone');
            $table->string('city', 100)->nullable()->after('address');
            
            // Extended medical history
            $table->text('chronic_diseases')->nullable()->after('medical_history');
            $table->text('current_medications')->nullable()->after('chronic_diseases');
            $table->text('previous_surgeries')->nullable()->after('current_medications');
            $table->text('family_history')->nullable()->after('previous_surgeries');
            
            // Emergency contact extension
            $table->string('emergency_relation', 50)->nullable()->after('emergency_contact');
            
            // Insurance info
            $table->string('insurance_provider')->nullable()->after('emergency_relation');
            $table->string('insurance_number', 50)->nullable()->after('insurance_provider');
            $table->date('insurance_expiry')->nullable()->after('insurance_number');
            
            // Additional fields
            $table->string('photo')->nullable()->after('insurance_expiry');
            $table->text('notes')->nullable()->after('photo');
            $table->enum('status', ['active', 'inactive', 'deceased'])->default('active')->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'patient_code',
                'name_en',
                'nationality',
                'id_number',
                'marital_status',
                'occupation',
                'phone_secondary',
                'city',
                'chronic_diseases',
                'current_medications',
                'previous_surgeries',
                'family_history',
                'emergency_relation',
                'insurance_provider',
                'insurance_number',
                'insurance_expiry',
                'photo',
                'notes',
                'status',
            ]);
        });
    }
};
