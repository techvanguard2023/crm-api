<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Service;
use App\Models\Customer;

echo "--- Starting Verification ---\n";

// 1. Create a Service (Catalog)
$service = Service::create([
    'name' => 'Website Maintenance',
    'description' => 'Monthly updates and backups'
]);
echo "Service Created: {$service->name}\n";

// 2. Create a Customer
$customer = Customer::create([
    'name' => 'Acme Corp',
    'email' => 'contact@acme.com',
    'phone' => '555-0199'
]);
echo "Customer Created: {$customer->name}\n";

// 3. Attach Service to Customer with Pivot Data
$customer->services()->attach($service->id, [
    'price' => 150.00,
    'recurrence' => 'monthly'
]);
echo "Service Attached to Customer with Price 150.00 and Recurrence 'monthly'\n";

// 4. Reload and Verify
$customer->refresh();
$attachedService = $customer->services->first();

if ($attachedService) {
    echo "Found Service: " . $attachedService->name . "\n";
    echo "Pivot Price: " . $attachedService->pivot->price . "\n";
    echo "Pivot Recurrence: " . $attachedService->pivot->recurrence . "\n";
    
    if ($attachedService->pivot->price == 150.00 && $attachedService->pivot->recurrence == 'monthly') {
        echo "SUCCESS: Pivot data matches.\n";
    } else {
        echo "FAILED: Pivot data mismatch.\n";
    }
} else {
    echo "FAILED: No service found attached.\n";
}
