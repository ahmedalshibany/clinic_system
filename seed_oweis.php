try {
// Patient
$p = App\Models\Patient::where('name', 'Oweis')->first();
if(!$p) {
    $p = App\Models\Patient::create([
        'name' => 'Oweis',
        'phone' => '1234567890',
        'age' => 30,
        'gender' => 'male'
    ]);
}
echo "Patient ID: " . $p->id . "\n";

// Doctor
$d = App\Models\Doctor::first();
if(!$d) {
    // Attempt with User ID?
    $d = App\Models\Doctor::create([
        'name' => 'Dr. Default',
        'specialty' => 'General',
        'is_active' => true
    ]);
}
echo "Doctor ID: " . $d->id . "\n";

// Appointment
$a = App\Models\Appointment::create([
    'patient_id' => $p->id,
    'doctor_id' => $d->id,
    'date' => now()->toDateString(),
    'time' => now()->toTimeString(),
    'status' => 'confirmed'
]);
echo "APPOINTMENT SUCCESS: ID " . $a->id . "\n";
} catch (\Exception $e) {
echo "ERROR: " . $e->getMessage() . "\n";
}
exit();
