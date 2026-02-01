<?php
// Headless Golden Master Runner
// Executes the full Patient Lifecycle via Laravel Application Logic

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

echo "--- GOLDEN MASTER RUN START ---\n";

// 0. Cleanup & Setup
$patientName = 'Grandpa Joe';
echo "[0] Cleanup: Removing old data for '$patientName'...\n";
$p = Patient::where('name', $patientName)->first();
if ($p) {
    Appointment::where('patient_id', $p->id)->delete();
    $p->delete();
}

$doctor = Doctor::first();
echo "[0] Setup: Creating fresh Patient and Appointment...\n";
$patient = Patient::create([
    'name' => $patientName,
    'phone' => '555-0000',
    'age' => 85,
    'gender' => 'male',
    'patient_code' => 'GM001'
]);

$appt = Appointment::create([
    'patient_id' => $patient->id,
    'doctor_id' => $doctor->id,
    'date' => now()->toDateString(),
    'time' => now()->format('H:i'),
    'status' => 'confirmed',
    'type' => 'Consultation',
    'fee' => 150.00
]);

echo "[0] Ready: Appointment ID #$appt->id (Status: $appt->status)\n\n";

// Phase 1: Receptionist - Check In
echo "[Phase 1] Receptionist: Check In...\n";
$receptionist = User::where('username', 'receptionist')->first() ?? User::factory()->create(['username' => 'receptionist', 'role' => 'receptionist']);
Auth::login($receptionist);

// Call CheckIn method directly
try {
    $controller = new App\Http\Controllers\AppointmentController();
    $controller->checkIn($appt);
    $appt->refresh();
    
    if ($appt->status === 'checked_in') {
        echo "✅ PASS: Appointment status is 'checked_in'.\n";
    } else {
        echo "❌ FAIL: Status is '$appt->status'.\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "❌ FAIL: Exception - " . $e->getMessage() . "\n";
    exit(1);
}

// Phase 2: Nurse - Record Vitals
echo "\n[Phase 2] Nurse: Record Vitals...\n";
$nurse = User::where('username', 'nurse_joy')->first() ?? User::factory()->create(['username' => 'nurse_joy', 'role' => 'nurse']);
Auth::login($nurse);

// Construct Request
$vitalsRequest = Request::create(route('nurse.vitals.store', $appt), 'POST', [
    'temperature' => '36.8',
    'bp_systolic' => '130',
    'bp_diastolic' => '85',
    'pulse' => '72',
    'weight' => '70',
    'notes' => 'Headless Verification'
]);

try {
    $nurseController = new App\Http\Controllers\NurseController();
    $nurseController->storeVitals($vitalsRequest, $appt);
    $appt->refresh();
    
    // Check DB
    $vital = $appt->vital;
    if ($vital && $vital->bp_systolic == 130) {
        echo "✅ PASS: Vitals saved (BP: 130/85).\n";
    } else {
        echo "❌ FAIL: Vitals not saved.\n";
        exit(1);
    }
    
    if ($appt->status === 'waiting') {
        echo "✅ PASS: Status transitioned to 'waiting' (Waiting Room).\n";
    } else {
        echo "❌ FAIL: Status did not update to 'waiting'. Current: $appt->status\n";
        // Phase 2 logic says it updates to waiting, so this is critical
        exit(1);
    }

} catch (Exception $e) {
    echo "❌ FAIL: Exception - " . $e->getMessage() . "\n";
    exit(1);
}

// Phase 3: Doctor - Complete Visit
echo "\n[Phase 3] Doctor: Consultation...\n";
$doctorUser = User::where('role', 'doctor')->first() ?? User::factory()->create(['role' => 'doctor', 'username' => 'doc']);
Auth::login($doctorUser);

try {
    // Start Visit
    $controller->startVisit($appt);
    $appt->refresh();
    if ($appt->status !== 'in_progress') {
         echo "❌ FAIL: Start Visit failed. Status: $appt->status\n";
         exit(1);
    }
    echo "✅ PASS: Visit Started (In Progress).\n";

    // Complete Visit
    $controller->complete($appt);
    $appt->refresh();
    if ($appt->status !== 'completed') {
         echo "❌ FAIL: Complete Visit failed. Status: $appt->status\n";
         exit(1);
    }
    echo "✅ PASS: Visit Completed.\n";

} catch (Exception $e) {
    echo "❌ FAIL: Exception - " . $e->getMessage() . "\n";
    exit(1);
}

// Phase 4: Receptionist - Billing
echo "\n[Phase 4] Receptionist: Billing...\n";
Auth::login($receptionist);

// Create Invoice
$invoiceRequest = Request::create(route('invoices.store'), 'POST', [
    'patient_id' => $patient->id,
    'appointment_id' => $appt->id,
    'due_date' => now()->addDays(30)->toDateString(),
    'status' => 'sent',
    'notes' => null,
    'items' => [
        [
            'description' => 'Consultation Fee',
            'quantity' => 1,
            'unit_price' => 150.00,
            'service_id' => null
        ]
    ]
]);

try {
    $invoiceController = new App\Http\Controllers\InvoiceController();
    $invoiceController->store($invoiceRequest);
    
    $invoice = App\Models\Invoice::where('appointment_id', $appt->id)->first();
    if ($invoice && $invoice->total == 150.00) {
        echo "✅ PASS: Invoice created (Total: 150.00).\n";
    } else {
        echo "❌ FAIL: Invoice creation failed.\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "❌ FAIL: Exception - " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n--- SYSTEM GOLD MASTER READY - SHIP IT ---\n";
