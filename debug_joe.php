<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$patients = App\Models\Patient::where('name', 'Grandpa Joe')->get();
echo "Found " . $patients->count() . " patients named Grandpa Joe.\n";

foreach ($patients as $p) {
    echo "ID: $p->id\n";
    foreach ($p->appointments as $a) {
        echo " - Appt ID: $a->id | Status: $a->status | Date: $a->date\n";
        $v = App\Models\Vital::where('appointment_id', $a->id)->first();
        echo "   - Vitals: " . ($v ? "YES" : "NO") . "\n";
    }
}
