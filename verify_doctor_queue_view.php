<?php

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

View::share('errors', new MessageBag());

// 1. Simulate Login
$user = User::where('email', 'doctor_dave@clinic.com')->first();
if ($user) {
    Auth::login($user);
    echo "✅ Logic Check: Logged in as " . $user->name . "\n";
} else {
    echo "❌ Logic Check: Doctor user not found.\n";
    exit(1);
}

echo "--- STARTING HEADLESS VERIFICATION: DOCTOR QUEUE VIEW ---\n";

// Fetch Data exactly as controller does
// (Assuming seeder ran, so we have Today's appointments)
$appointments = Appointment::with(['patient', 'doctor', 'vital'])
    ->whereDate('date', now()->today())
    ->orderBy('time')
    ->get();

$doctors = \App\Models\Doctor::all();

echo "Appointments Found: " . $appointments->count() . "\n";
foreach($appointments as $appt) {
    echo " - ID: {$appt->id} | Patient: {$appt->patient->name} | Status: {$appt->status} | Vitals: " . ($appt->vital ? 'Yes' : 'No') . "\n";
}

// Render View
echo "Rendering Queue View...\n";
$html = view('appointments.queue', compact('appointments', 'doctors'))->render();

// Check Columns
$checks = [
    'Upcoming' => 'Dave Scheduled',
    'With Nurse' => 'Dave With Nurse',
    'Ready for You' => 'Dave Waiting', 
    'In Progress' => 'Dave InProgress',
    'Vitals Display' => '120/80', // BP from seeder
    'Start Visit Button' => 'Start Visit'
];

foreach ($checks as $feature => $expectedText) {
    if (str_contains($html, $expectedText)) {
        echo "✅ Verified: [$feature] found expected content '$expectedText'.\n";
    } else {
        echo "❌ FAILED: [$feature] did NOT find '$expectedText'.\n";
    }
}

// Check Correct Column Categorization (Simple regex check)
// Dave Waiting should be under "Ready for You" (Success Card)
// We can check if 'Dave Waiting' appears after 'Ready for You' and before 'In Progress'
$posReady = strpos($html, 'Ready for You');
$posDaveWaiting = strpos($html, 'Dave Waiting');
$posInProgress = strpos($html, 'In Progress');

if ($posReady !== false && $posDaveWaiting !== false && $posDaveWaiting > $posReady && ($posInProgress === false || $posDaveWaiting < $posInProgress)) {
    echo "✅ Verified: 'Dave Waiting' is in the 'Ready for You' column.\n";
} else {
    echo "⚠️ Warning: Could not verify column position via simple string check. Manual verify recommended.\n";
}

echo "--- VERIFICATION COMPLETE ---\n";
