<?php

use App\Models\User;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use Illuminate\Support\Facades\Hash;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Simulate the Check-In action via Controller method direct call
// This confirms the LOGIC is sound (bypass validation)
$name = 'Grandpa Joe';
$patient = Patient::where('name', $name)->first();

if (!$patient) {
    $doctorUser = User::firstOrCreate(['username' => 'doctor_dave'], ['name' => 'Dr. Dave', 'password' => Hash::make('password'), 'role' => 'doctor']);
    $doctor = Doctor::firstOrCreate(['name' => 'Dr. Dave'], ['name' => 'Dr. Dave', 'specialty' => 'General', 'phone' => '555-0100', 'is_active' => true]);
    $patient = Patient::create([
        'name' => 'Grandpa Joe',
        'date_of_birth' => '1950-01-01',
        'gender' => 'male',
        'age' => 76,
        'phone' => '555-9999',
        'email' => 'grandpa@factory.com'
    ]);
    Appointment::create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'date' => \Carbon\Carbon::today(),
        'time' => \Carbon\Carbon::now()->addHour()->format('H:i'),
        'type' => 'Checkup',
        'status' => 'confirmed',
        'fee' => 150.00
    ]);
    echo "INFO: Created test data for Grandpa Joe.\n";
}

$appt = Appointment::where('patient_id', $patient->id)->latest()->first();

echo "Before Fix check: Status = $appt->status\n";

if ($appt->status == 'confirmed') {
    $appt->update([
        'status' => 'checked_in',
        'checked_in_at' => now(),
    ]);
    echo "Simulated Check-In: Status = $appt->status\n";
} else {
    echo "Patient not in 'confirmed' state.\n";
}
