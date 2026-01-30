<?php

use App\Models\User;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting Data Seeding for Receptionist Billing...\n";

// 1. Ensure Receptionist User
$receptionistEmail = 'receptionist_amy@clinic.com';
$receptionist = User::where('email', $receptionistEmail)->first();

if (!$receptionist) {
    echo "Creating Receptionist Amy...\n";
    $receptionist = User::create([
        'name' => 'Amy Receptionist',
        'username' => 'receptionist_amy',
        'email' => $receptionistEmail,
        'role' => 'receptionist',
        'password' => Hash::make('password123'),
        'phone' => '555-0909',
        'is_active' => true,
    ]);
} else {
    echo "Receptionist Amy already exists.\n";
    $receptionist->role = 'receptionist';
    $receptionist->save();
}

// 2. Ensure Patients and Doctors exist
$patient = Patient::first();
if (!$patient) {
    $patient = Patient::create([
        'name' => 'Billing Test Patient',
        'gender' => 'female',
        'phone' => '555-9999',
        'date_of_birth' => '1985-05-05',
        'status' => 'active'
    ]);
}

$doctor = Doctor::first();

// 3. Create Completed Appointment (Ready for Billing)
// Ensure no invoice exists for this appointment
echo "Creating Completed Appointment (Ready to Bill)...\n";
$appt = Appointment::create([
    'patient_id' => $patient->id,
    'doctor_id' => $doctor->id,
    'date' => Carbon::today(),
    'time' => Carbon::now()->subHours(2)->format('H:i'),
    'status' => 'completed',
    'type' => 'checkup',
    'notes' => 'Completed - Ready for Billing'
]);

// Explicitly ensure no invoice is attached (though it's new, so it shouldn't have one)
// If you want to be extra sure, check if relationship exists and delete it, but for a new record it's fine.

echo "Seeding Complete. Appointment ID: " . $appt->id . "\n";
