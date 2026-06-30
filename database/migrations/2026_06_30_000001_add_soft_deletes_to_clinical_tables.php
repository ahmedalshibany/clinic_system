<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_records', fn (Blueprint $t) => $t->softDeletes());
        Schema::table('prescriptions', fn (Blueprint $t) => $t->softDeletes());
        Schema::table('prescription_items', fn (Blueprint $t) => $t->softDeletes());
        Schema::table('vitals', fn (Blueprint $t) => $t->softDeletes());
        Schema::table('appointments', fn (Blueprint $t) => $t->softDeletes());
        Schema::table('patient_files', fn (Blueprint $t) => $t->softDeletes());
    }

    public function down(): void
    {
        Schema::table('medical_records', fn (Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('prescriptions', fn (Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('prescription_items', fn (Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('vitals', fn (Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('appointments', fn (Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('patient_files', fn (Blueprint $t) => $t->dropSoftDeletes());
    }
};
