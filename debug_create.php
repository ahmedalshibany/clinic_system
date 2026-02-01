<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Patient;
use Illuminate\Support\Facades\DB;

echo "Attempting to create patient...\n";
try {
    $p = Patient::create([
        'name' => 'Debug Patient',
        'age' => 30,
        'gender' => 'male',
        'phone' => '1234567890',
        'date_of_birth' => '1995-01-01'
    ]);
    echo "Patient created successfully: ID " . $p->id . "\n";
    echo "Patient Code: " . $p->patient_code . "\n";
    echo "Status: " . $p->status . "\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
