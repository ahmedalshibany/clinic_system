<?php

use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Vital;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- SEEDING DOCTOR QUEUE ---\n";

// 1. Ensure Doctor Exists
$doctorUser = User::firstOrCreate(
    ['email' => 'doctor_dave@clinic.com'],
    [
        'name' => 'Dr. Dave',
        'username' => 'doctor_dave',
        'password' => Hash::make('password123'),
        'role' => 'doctor',
    ]
);

$doctor = Doctor::firstOrCreate(
    ['email' => 'doctor_dave@clinic.com'],
    [
        'name' => 'Dr. Dave',
        'specialty' => 'General Practice',
        'phone' => '555-0199',
        'is_active' => true,
    ]
);

echo "✅ Doctor: " . $doctor->name . "\n";

// 2. Clear Today's Appointments for this Doctor (to start clean)
Appointment::where('doctor_id', $doctor->id)->whereDate('date', Carbon::today())->delete();

// 3. Create Patients
$patients = [
    'Scheduled' => Patient::firstOrCreate(['email' => 'p_scheduled@test.com'], ['name' => 'Dave Scheduled', 'phone' => '555-0001', 'dob' => '1990-01-01', 'gender' => 'male', 'age' => 34]),
    'Confirmed' => Patient::firstOrCreate(['email' => 'p_confirmed@test.com'], ['name' => 'Dave With Nurse', 'phone' => '555-0002', 'dob' => '1990-01-01', 'gender' => 'female', 'age' => 34]),
    'Waiting' => Patient::firstOrCreate(['email' => 'p_waiting@test.com'], ['name' => 'Dave Waiting', 'phone' => '555-0003', 'dob' => '1990-01-01', 'gender' => 'male', 'age' => 34]),
    'InProgress' => Patient::firstOrCreate(['email' => 'p_inprogress@test.com'], ['name' => 'Dave InProgress', 'phone' => '555-0004', 'dob' => '1990-01-01', 'gender' => 'female', 'age' => 34]),
];

// 4. Create Appointments
// A. Scheduled (Late afternoon)
Appointment::create([
    'patient_id' => $patients['Scheduled']->id,
    'doctor_id' => $doctor->id,
    'date' => Carbon::today(),
    'time' => '16:00:00',
    'type' => 'Consultation',
    'status' => 'scheduled',
]);
echo "✅ Created: Scheduled Appointment\n";

// B. Confirmed (With Nurse - Early morning)
Appointment::create([
    'patient_id' => $patients['Confirmed']->id,
    'doctor_id' => $doctor->id,
    'date' => Carbon::today(),
    'time' => '09:00:00',
    'type' => 'Checkup',
    'status' => 'confirmed',
    'checked_in_at' => Carbon::now()->subMinutes(30),
]);
echo "✅ Created: Confirmed Appointment (With Nurse)\n";

// C. Waiting (Ready for Doctor - Vitals Done)
$waitingAppt = Appointment::create([
    'patient_id' => $patients['Waiting']->id,
    'doctor_id' => $doctor->id,
    'date' => Carbon::today(),
    'time' => '09:30:00',
    'type' => 'Consultation',
    'status' => 'waiting',
    'checked_in_at' => Carbon::now()->subMinutes(45),
]);

// Add Vitals
Vital::create([
    'appointment_id' => $waitingAppt->id,
    'created_by' => User::where('role', 'nurse')->first()->id ?? 1,
    'temperature' => 37.2,
    'bp_systolic' => 120,
    'bp_diastolic' => 80,
    'pulse' => 72,
    'weight' => 70,
    'height' => 175,
    'notes' => 'Patient complains of headache.',
]);
echo "✅ Created: Waiting Appointment + Vitals\n";

// D. In Progress
Appointment::create([
    'patient_id' => $patients['InProgress']->id,
    'doctor_id' => $doctor->id,
    'date' => Carbon::today(),
    'time' => '09:15:00',
    'type' => 'Follow-up',
    'status' => 'in_progress',
    'checked_in_at' => Carbon::now()->subMinutes(60),
    'started_at' => Carbon::now()->subMinutes(10),
]);
echo "✅ Created: In Progress Appointment\n";

echo "--- SEEDING COMPLETE ---\n";
