<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

auth()->loginUsingId(1);
$p = App\Models\Patient::latest()->first();

if(!$p) {
    echo "Creating fake patient...\n";
    $p = App\Models\Patient::create([
        'name' => 'John Debug',
        'age' => 45,
        'gender' => 'male',
        'phone' => '999-999-9999'
    ]);
}

echo "Updating patient...\n";
$request = \Illuminate\Http\Request::create('/patients/'.$p->id, 'PUT', [
    'name' => 'John Debug Updated',
    'age' => 46,
    'gender' => 'male',
    'phone' => $p->phone,
    'id_number' => $p->id_number,
    'email' => $p->email,
]);

try {
    app(App\Http\Controllers\PatientController::class)->update($request, $p);
    echo "Update successful.\n";
} catch (\Exception $e) {
    echo "Update Error: " . $e->getMessage() . "\n";
}

echo "Deleting patient...\n";
try {
    app(App\Http\Controllers\PatientController::class)->destroy($p);
    echo "Destroy successful.\n";
} catch (\Exception $e) {
    echo "Destroy Error: " . $e->getMessage() . "\n";
}
