<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "Columns in 'patients' table:\n";
$columns = DB::select('SHOW COLUMNS FROM patients');
foreach ($columns as $c) {
    echo "Field: " . str_pad($c->Field, 20) . " | Type: " . str_pad($c->Type, 15) . " | Null: " . str_pad($c->Null, 5) . " | Default: " . ($c->Default ?? 'NULL') . "\n";
}
