<?php

use App\Models\User;
use App\Models\Appointment;
use App\Models\Vital;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\MessageBag;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

View::share('errors', new MessageBag());

function verify_visual($element, $html, $expectedClass, $description) {
    if (str_contains($html, $expectedClass)) {
        echo "✅ VISUAL PASS: [$element] has class '$expectedClass' ($description)\n";
    } else {
        echo "❌ VISUAL FAIL: [$element] missing class '$expectedClass'\n";
        // Context debug
        if (str_contains($html, 'badge')) {
           preg_match('/<span class="badge[^>]*>(.*?)<\/span>/s', $html, $matches);
           echo "   -> FOUND BADGE: " . ($matches[0] ?? 'N/A') . "\n"; 
        }
    }
}

echo "--- STARTING VISUAL DOM & CSS ANALYSIS ---\n";

// ----------------------------------------------------------------
// 1. RECEPTIONIST: Appointment Status Badge
// ----------------------------------------------------------------
echo "\n--- 1. RECEPTIONIST VIEW ---\n";
// Login
$receptionist = User::where('role', 'receptionist')->first();
if (!$receptionist) $receptionist = User::factory()->create(['role' => 'receptionist']);
Auth::login($receptionist);

// Create Data
$patient = Patient::first(); 
$doctor = Doctor::first();
$appt1 = Appointment::create([
    'patient_id' => $patient->id,
    'doctor_id' => $doctor->id,
    'date' => now(), 
    'time' => now(), 
    'type' => 'Checkup',
    'status' => 'confirmed'
]);

// Verify
$appointments = Appointment::with(['patient', 'doctor'])->where('id', $appt1->id)->paginate(10);
$patients = collect([$patient]);
$doctors = collect([$doctor]);

$html = view('appointments.index', compact('appointments', 'patients', 'doctors'))->render();

if (str_contains($html, 'No appointments found')) {
    echo "❌ DEBUG: Table is EMPTY. Appointment ID {$appt1->id} not found in Paginator.\n";
} else {
    // Check for 'bg-success' (confirmed)
    verify_visual("Status Badge", $html, 'bg-success', "Green/Success Color");
}

// Cleanup
$appt1->delete();


// ----------------------------------------------------------------
// 2. NURSE: Dashboard Cleanliness & Buttons
// ----------------------------------------------------------------
echo "\n--- 2. NURSE VIEW ---\n";
$nurse = User::where('role', 'nurse')->first();
if (!$nurse) $nurse = User::factory()->create(['role' => 'nurse']);
Auth::login($nurse);

// Mock Data for Dashboard
$triageQueue = collect([]);
$waitingList = collect([]);
$readyToBillCount = 0;
// Full Stats Mock
$totalAppointments = 10;
$todayAppointments = 5;
$pending = 2;
$confirmed = 3;
$completed = 4;
$cancelled = 1;
$todayRevenue = 1000;
$newPatientsMonth = 5;
$pendingInvoicesCount = 2;
$pendingInvoicesAmount = 300;
$waitingPatients = 1;
$weekAppointments = 8;
$monthAppointments = 20;
$recentAppointments = collect([]);
$weeklyData = [];

$html = view('dashboard', compact(
    'triageQueue', 'waitingList', 'readyToBillCount', 
    'totalAppointments', 'todayAppointments', 'pending', 'confirmed', 'completed', 'cancelled',
    'todayRevenue', 'newPatientsMonth', 'pendingInvoicesCount', 'pendingInvoicesAmount',
    'waitingPatients', 'weekAppointments', 'monthAppointments', 'recentAppointments', 'weeklyData'
))->render();

// Check Security
if (!str_contains($html, 'Earnings Report') && !str_contains($html, 'Revenue')) {
    echo "✅ VISUAL PASS: [Security] Admin Revenue Cards are NOT present in DOM.\n";
} else {
    echo "❌ VISUAL FAIL: [Security] Earnings/Revenue text FOUND in DOM.\n";
}

// Check Button
verify_visual("Action Button", $html, 'btn-primary', "Primary Color");


// ----------------------------------------------------------------
// 3. DOCTOR: Kanban Board Colors
// ----------------------------------------------------------------
echo "\n--- 3. DOCTOR VIEW ---\n";
$doctorUser = User::where('role', 'doctor')->first();
if (!$doctorUser) $doctorUser = User::factory()->create(['role' => 'doctor']);
Auth::login($doctorUser);

// Create Data
$appt2 = Appointment::create([
    'patient_id' => $patient->id,
    'doctor_id' => $doctor->id,
    'date' => now(), 
    'time' => now(), 
    'type' => 'Consultation',
    'status' => 'waiting',
    'checked_in_at' => now()->subMinutes(30)
]);

try {
    Vital::create([
        'appointment_id' => $appt2->id,
        'bp_systolic' => 120, 
        'bp_diastolic' => 80, 
        'temperature' => 37.5,
        'pulse' => 72,
        'weight' => 80.0,
        'height' => 180.0,
        'respiratory_rate' => 18,
        'created_by' => $nurse->id
    ]);
} catch (\Exception $e) {
    echo "❌ CRITICAL: Failed to create Vital: " . $e->getMessage() . "\n";
}

// Reload
$appt2 = Appointment::with(['patient', 'vital', 'doctor'])->find($appt2->id);

$appointments = collect([$appt2]);
$doctors = collect([$doctor]);

$html = view('appointments.queue', compact('appointments', 'doctors'))->render();

// Check Kanban Visuals
verify_visual("Ready Column Header", $html, 'bg-success', "Green Header");
verify_visual("Patient Card", $html, 'border-success', "Green Border");

// Check Vitals Content
if (str_contains($html, '120/80') && str_contains($html, '37.5')) {
    echo "✅ VISUAL PASS: [Content] Vitals (120/80) Visible on Card.\n";
} else {
    echo "❌ VISUAL FAIL: [Content] Vitals text NOT found.\n";
}

// Cleanup
if ($appt2->vital) $appt2->vital->delete();
$appt2->delete();

echo "\n--- ANALYSIS COMPLETE ---\n";
