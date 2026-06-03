<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach (\App\Models\User::all() as $u) {
    echo $u->username . ' | role=' . $u->role . ' | is_active=' . ($u->is_active ? '1' : '0') . PHP_EOL;
}
