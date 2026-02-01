<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$name = 'Grandpa Joe';
$patient = App\Models\Patient::where('name', $name)->first();

if (!$patient) {
    echo "FAIL: Patient verified (Not Found)\n";
    exit;
}

$appt = App\Models\Appointment::where('patient_id', $patient->id)->latest()->first();
echo "Phase 1: Check In Status: [" . ($appt->status == 'checked_in' || $appt->status == 'waiting' ? 'PASS' : 'FAIL') . "] (Current: $appt->status)\n";

$vitals = App\Models\Vital::where('appointment_id', $appt->id)->first();
if ($vitals) {
    $bp = $vitals->blood_pressure_systolic . '/' . $vitals->blood_pressure_diastolic;
    echo "Phase 2: Nurse Vitals: [PASS] (BP: $bp)\n";
} else {
    echo "Phase 2: Nurse Vitals: [FAIL]\n";
}

if ($appt->status === 'waiting' || ($appt->status === 'checked_in' && $vitals)) {
     echo "Phase 2: Waiting Room Visibility: [PASS] (Inferred from Status+Vitals)\n";
} else {
     echo "Phase 2: Waiting Room Visibility: [CHECK REQUIRED]\n";
}
