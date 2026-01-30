<?php

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- STARTING HEADLESS VERIFICATION: INVOICE CONTROLLER ---\n";

// 1. Simulate Login
$user = User::where('email', 'receptionist_amy@clinic.com')->first();
Auth::login($user);

// 2. Find the Test Appointment
$appointment = Appointment::where('status', 'completed')
    ->doesntHave('invoice')
    ->orderBy('id', 'desc')
    ->first();

if (!$appointment) {
    die("❌ Error: No ready-to-bill appointment found. Run seeder first.\n");
}
echo "✅ Found Appointment ID: " . $appointment->id . " (Fee: " . $appointment->fee . ")\n";

// 3. Test Controller Logic directly
try {
    $controller = new \App\Http\Controllers\InvoiceController();
    
    // Call the method
    // Since it returns a View, we can inspect the View's data
    $view = $controller->createFromAppointment($appointment);
    $data = $view->getData();

    // Check 1: Patient Selection
    if ($data['selected_patient']->id === $appointment->patient_id) {
        echo "✅ Data Check: Appt Patient is pre-selected.\n";
    } else {
        echo "❌ Data Check: Patient mismatch.\n";
    }

    // Check 2: Appointment Link
    if ($data['appointment']->id === $appointment->id) {
        echo "✅ Data Check: Appointment object passed to view.\n";
    } else {
        echo "❌ Data Check: Appointment object missing.\n";
    }

    // Check 3: Prefilled Items
    if (isset($data['prefilled_items']) && count($data['prefilled_items']) > 0) {
        $item = $data['prefilled_items'][0];
        echo "✅ Data Check: Prefilled Item found: " . $item['description'] . "\n";
        echo "   - Price: " . $item['unit_price'] . "\n";
    } else {
        echo "❌ Data Check: No prefilled items found.\n";
    }
    
    echo "✅ Controller Method executed successfully.\n";

} catch (\Exception $e) {
    echo "❌ Controller Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

echo "--- VERIFICATION COMPLETE ---\n";
