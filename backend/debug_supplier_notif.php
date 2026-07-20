<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Simulate what Filament does: update a supplier to inactive
$supplier = \App\Models\Supplier::where('is_active', true)->first();
if ($supplier) {
    echo "Toggling supplier '{$supplier->company_name}' to inactive..." . PHP_EOL;
    $supplier->is_active = false;
    $supplier->save();
    echo "Saved. wasChanged: " . var_export($supplier->wasChanged('is_active'), true) . PHP_EOL;

    // Check notifications after save
    $admin = \App\Models\User::role('super_admin')->first();
    $latest = $admin->notifications()->latest()->first();
    if ($latest) {
        echo "Latest notification: " . $latest->data['title'] . PHP_EOL;
        echo "Body: " . $latest->data['body'] . PHP_EOL;
    }

    // Restore to active
    $supplier->is_active = true;
    $supplier->save();
    echo "Restored supplier back to active." . PHP_EOL;
} else {
    echo "No active supplier found to test." . PHP_EOL;
}
