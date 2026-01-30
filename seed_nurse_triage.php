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

echo "Starting Data Seeding for Nurse Triage...\n";

// 1. Ensure Nurse User
$nurseEmail = 'nurse_betty@clinic.com';
$nurse = User::where('email', $nurseEmail)->first();

if (!$nurse) {
    echo "Creating Nurse Betty...\n";
    $nurse = User::create([
        'name' => 'Nurse Betty',
        'username' => 'nurse_betty',
        'email' => $nurseEmail,
        'role' => 'nurse',
        'password' => Hash::make('password123'),
        'phone' => '555-0101',
        'is_active' => true,
    ]);
} else {
    echo "Nurse Betty already exists.\n";
    $nurse->role = 'nurse'; // Ensure role is correct
    $nurse->save();
}

// 2. Ensure Patients and Doctors exist
$patient = Patient::first();
if (!$patient) {
    echo "Creating dummy patient...\n";
    $patient = Patient::create([
        'name' => 'John Doe',
        'gender' => 'male',
        'phone' => '555-1234',
        'date_of_birth' => '1990-01-01',
        'status' => 'active'
    ]);
}

$doctor = Doctor::first();
if (!$doctor) {
    echo "Creating dummy doctor...\n";
    $doctor = Doctor::create([
        'name' => 'Dr. Smith',
        'specialization' => 'General',
        'phone' => '555-5678',
        'email' => 'dr.smith@clinic.com',
        'status' => 'active'
    ]);
}

// 3. Create Triage Appointment (Today, Confirmed)
echo "Creating Confirmed Appointment (Triage Queue)...\n";
Appointment::create([
    'patient_id' => $patient->id,
    'doctor_id' => $doctor->id,
    'date' => Carbon::today(),
    'time' => Carbon::now()->addHour()->format('H:i'),
    'status' => 'confirmed',
    'type' => 'checkup',
    'notes' => 'Triage Test Appointment'
]);

// 4. Create Waiting Appointment (Today, Waiting)
echo "Creating Waiting Appointment (Waiting Room)...\n";
Appointment::create([
    'patient_id' => $patient->id,
    'doctor_id' => $doctor->id,
    'date' => Carbon::today(),
    'time' => Carbon::now()->subHour()->format('H:i'),
    'status' => 'waiting',
    'type' => 'checkup',
    'notes' => 'Waiting Test Appointment'
]);

echo "Seeding Complete.\n";
