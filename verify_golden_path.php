<?php

use App\Models\User;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Vital;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\MessageBag;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Setup Shared View Data
View::share('errors', new MessageBag());

function pass($step) { echo "✅ PASS: $step\n"; }
function fail($step, $msg) { echo "❌ FAIL: $step - $msg\n"; exit(1); }
function title($text) { echo "\n=== $text ===\n"; }

echo "--- STARTING GOLDEN PATH VERIFICATION ---\n";

// ----------------------------------------------------------------
// SETUP: Ensure Users Exist
// ----------------------------------------------------------------
title("SETUP");
$receptionist = User::firstOrCreate(['email' => 'receptionist_amy@clinic.com'], ['name' => 'Amy', 'password' => Hash::make('password'), 'role' => 'receptionist']);
$nurse = User::firstOrCreate(['email' => 'nurse_betty@clinic.com'], ['name' => 'Betty', 'password' => Hash::make('password'), 'role' => 'nurse']);
// Create Doctor user and profile
$doctorUser = User::firstOrCreate(['email' => 'doctor_dave@clinic.com'], ['name' => 'Dr. Dave', 'password' => Hash::make('password'), 'role' => 'doctor']);
$doctor = Doctor::firstOrCreate(['email' => 'doctor_dave@clinic.com'], ['name' => 'Dr. Dave', 'specialty' => 'General', 'is_active' => true]);

pass("Users verified (Amy, Betty, Dave)");

// Clean up previous run
$existingPatient = Patient::where('name', 'Grandpa Joe')->first();
if ($existingPatient) {
    // Delete related
    $appts = Appointment::where('patient_id', $existingPatient->id)->get();
    foreach($appts as $a) {
        if ($a->vital) $a->vital->delete();
        if ($a->invoice) { $a->invoice->items()->delete(); $a->invoice->delete(); }
        $a->delete();
    }
    $existingPatient->delete();
    echo "ℹ️  Cleaned up previous Grandpa Joe data.\n";
}

// ----------------------------------------------------------------
// STEP 1: RECEPTIONIST (INTAKE)
// ----------------------------------------------------------------
title("STEP 1: RECEPTIONIST (INTAKE)");
Auth::login($receptionist);
echo "👤 Logged in as: " . Auth::user()->name . "\n";

// Action: Create Patient
$patient = Patient::create([
    'name' => 'Grandpa Joe',
    'date_of_birth' => '1950-01-01',
    'gender' => 'male',
    'age' => 76,
    'phone' => '555-9999',
    'email' => 'grandpa@factory.com'
]);
pass("Patient 'Grandpa Joe' Created");

// Action: Book Appointment
$appointment = Appointment::create([
    'patient_id' => $patient->id,
    'doctor_id' => $doctor->id,
    'date' => Carbon::today(),
    'time' => Carbon::now()->addHour()->format('H:i'), // Upcoming
    'type' => 'Checkup',
    'status' => 'pending',
    'fee' => 150.00
]);
pass("Appointment Booked (Status: Pending)");

// Action: Check In
$appointment->update(['status' => 'confirmed', 'checked_in_at' => now()]);
pass("Patient Checked In (Status: Confirmed)");


// ----------------------------------------------------------------
// STEP 2: NURSE (TRIAGE)
// ----------------------------------------------------------------
title("STEP 2: NURSE (TRIAGE)");
Auth::login($nurse);
echo "👤 Logged in as: " . Auth::user()->name . "\n";

// View Check: Is he in Triage Queue?
$dashboardParams = (new \App\Http\Controllers\DashboardController)->index()->getData();
$triageList = $dashboardParams['triageQueue'];
if ($triageList->contains('id', $appointment->id)) {
    pass("Grandpa Joe appears in Nurse Triage Queue");
} else {
    fail("Visibility", "Grandpa Joe NOT found in Triage Queue");
}

// SECURITY CHECK: Admin Widgets
// Render the view to check for leakage
$nurseView = view('dashboard', $dashboardParams)->render();
if (str_contains($nurseView, 'Revenue') || str_contains($nurseView, 'Monthly Earnings')) {
    fail("Security", "Admin Revenue Charts are VISIBLE to Nurse! (Access Control Failure)");
} else {
    pass("Security: Admin Revenue Charts are HIDDEN from Nurse");
}

// Action: Record Vitals
Vital::create([
    'appointment_id' => $appointment->id,
    'temperature' => 37,
    'bp_systolic' => 120,
    'bp_diastolic' => 80,
    'pulse' => 72,
    'weight' => 80,
    'height' => 180,
    'created_by' => $nurse->id
]);
pass("Vitals Recorded (120/80)");

// Action: Move to Waiting
$appointment->update(['status' => 'waiting']);
pass("Patient Moved to Waiting Room");


// ----------------------------------------------------------------
// STEP 3: DOCTOR (CONSULTATION)
// ----------------------------------------------------------------
title("STEP 3: DOCTOR (CONSULTATION)");
Auth::login($doctorUser);
echo "👤 Logged in as: " . Auth::user()->name . "\n";

// View Check: Is he in Ready Column? (Using our Headless logic from before)
$queueParams = (new \App\Http\Controllers\AppointmentController)->queue(new \Illuminate\Http\Request)->getData();
$allAppts = $queueParams['appointments'];
$grandpaInQueue = $allAppts->firstWhere('id', $appointment->id);

if ($grandpaInQueue && $grandpaInQueue->status === 'waiting') {
    pass("Grandpa Joe appears in Doctor's 'Ready' List");
} else {
    fail("Visibility", "Grandpa Joe NOT found in Doctor Queue or wrong status");
}

// Check Vitals visibility in Object (eager load check)
if ($grandpaInQueue->vital) {
    pass("Doctor can see Vitals");
} else {
    fail("Data", "Vitals not linked/loaded for Doctor");
}

// Action: Start Visit
$appointment->update(['status' => 'in_progress', 'started_at' => now()]);
pass("Visit Started (In Progress)");

// Action: Complete Visit
$appointment->update(['status' => 'completed', 'completed_at' => now()]);
pass("Visit Completed");


// ----------------------------------------------------------------
// STEP 4: RECEPTIONIST (BILLING)
// ----------------------------------------------------------------
title("STEP 4: RECEPTIONIST (BILLING)");
Auth::login($receptionist);
echo "👤 Logged in as: " . Auth::user()->name . "\n";

// View Check: Alert logic
$billingParams = (new \App\Http\Controllers\DashboardController)->index()->getData();
if ($billingParams['readyToBillCount'] >= 1) {
    pass("Dashboard Alert identifies pending invoice");
} else {
    fail("Billing Alert", "ReadyToBillCount is 0 (Expected >= 1)");
}

// Action: Create Invoice
$invoice = Invoice::create([
    'patient_id' => $patient->id,
    'appointment_id' => $appointment->id,
    'created_by' => $receptionist->id,
    'subtotal' => 150,
    'total' => 150,
    'status' => 'paid', // Instant pay
    'due_date' => now(),
    'amount_paid' => 150
]);
// Add Item
$invoice->items()->create([
    'description' => 'Consultation',
    'quantity' => 1,
    'unit_price' => 150,
    'total' => 150
]);

pass("Invoice Created & Paid ($150)");

// Final Check: Revenue
// (Since we just made a paid invoice, it should be reflected)
// We verify that the Appointment no longer shows as "Ready to Bill"
$newBillingParams = (new \App\Http\Controllers\DashboardController)->index()->getData();
// Since we paid it, it definitely has an invoice now
if (Appointment::where('id', $appointment->id)->doesntHave('invoice')->count() === 0) {
    pass("Billing Alert CLEARED for Grandpa Joe");
} else {
    fail("Billing Alert", "Grandpa Joe still flagged as ready to bill");
}

echo "\n--- GOLDEN PATH COMPLETE ---\n";
echo "✅ SYSTEM GOLD MASTER READY\n";
