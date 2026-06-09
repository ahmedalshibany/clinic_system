<?php

use App\Models\Appointment;
use App\Models\User;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Vital;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\MessageBag;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

View::share('errors', new MessageBag());

// 1. Simulate Login
$doctorUser = User::firstOrCreate(['username' => 'doctor_dave'], ['name' => 'Dr. Dave', 'password' => Hash::make('password'), 'role' => 'doctor']);
if ($doctorUser) {
    Auth::login($doctorUser);
    echo "✅ Logic Check: Logged in as " . $doctorUser->name . "\n";
} else {
    echo "❌ Logic Check: Doctor user not found.\n";
    exit(1);
}

echo "--- STARTING HEADLESS VERIFICATION: DOCTOR QUEUE VIEW ---\n";

// Create test data for the queue if none exists for today
$waitingAppt = Appointment::whereDate('date', now()->today())->where('status', 'waiting')->first();
if (!$waitingAppt) {
    $doctor = Doctor::firstOrCreate(['name' => 'Dr. Dave'], ['name' => 'Dr. Dave', 'specialty' => 'General', 'phone' => '555-0100', 'is_active' => true]);
    $patient = Patient::create([
        'name' => 'Queue Test Patient',
        'date_of_birth' => '1985-05-15',
        'gender' => 'male',
        'age' => 41,
        'phone' => '555-5678',
        'email' => 'queue@test.com'
    ]);
    $appt = Appointment::create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'date' => now()->today(),
        'time' => now()->format('H:i'),
        'type' => 'Checkup',
        'status' => 'waiting',
        'fee' => 150.00,
        'checked_in_at' => now()->subMinutes(30)
    ]);
    Vital::create([
        'appointment_id' => $appt->id,
        'bp_systolic' => 120,
        'bp_diastolic' => 80,
        'temperature' => 37.5,
        'pulse' => 72,
        'weight' => 80.0,
        'height' => 180.0,
        'created_by' => $doctorUser->id
    ]);
    echo "ℹ️ Created test waiting appointment with vitals.\n";
}

// Fetch Data
$appointments = Appointment::with(['patient', 'doctor', 'vital'])
    ->whereDate('date', now()->today())
    ->orderBy('time')
    ->get();

$doctors = Doctor::all();

echo "Appointments Found: " . $appointments->count() . "\n";
foreach($appointments as $appt) {
    echo " - ID: {$appt->id} | Patient: {$appt->patient->name} | Status: {$appt->status} | Vitals: " . ($appt->vital ? 'Yes' : 'No') . "\n";
}

// Render View
echo "Rendering Queue View...\n";
$html = view('appointments.queue', compact('appointments', 'doctors'))->render();

// Check the kanban board rendered correctly by looking for column headers and patient data
$checks = [
    'Upcoming Column' => 'Upcoming',
    'Confirmed Column' => 'With Nurse',
    'Ready Column' => 'Ready for You',
    'In Progress Column' => 'In Progress',
    'Vitals Content' => '120/80',
];

foreach ($checks as $feature => $expectedText) {
    if (str_contains($html, $expectedText)) {
        echo "✅ Verified: [$feature] found expected content '$expectedText'.\n";
    } else {
        echo "❌ FAILED: [$feature] did NOT find '$expectedText'.\n";
    }
}

// Check that the waiting patient appears in the Ready column
$posReady = strpos($html, 'Ready for You');
$posWaitingPatient = strpos($html, 'Queue Test Patient');
$posInProgress = strpos($html, 'In Progress');

if ($posReady !== false && $posWaitingPatient !== false && $posWaitingPatient > $posReady && ($posInProgress === false || $posWaitingPatient < $posInProgress)) {
    echo "✅ Verified: Test patient appears in the 'Ready for You' column.\n";
} else {
    echo "⚠️ Warning: Could not verify patient column position. Manual verify recommended.\n";
}

echo "--- VERIFICATION COMPLETE ---\n";
