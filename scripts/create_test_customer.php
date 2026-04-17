<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;

try {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'testuser+' . time() . '@example.com',
        'location' => 'Test City',
        'phone' => '999' . rand(1000,9999),
        'role' => 'customer',
        'password' => Hash::make('password123'),
    ]);

    $cust = Customer::create([
        'user_id' => $user->id,
        'primary_device' => 'TestPhone',
    ]);

    echo "Created user id={$user->id} customer_id={$cust->customer_id}\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
