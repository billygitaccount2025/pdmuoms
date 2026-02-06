<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Auth\Events\Registered;

echo "\n=== Testing User Registration and Email Sending ===\n\n";

try {
    // Create a test user
    $user = User::create([
        'fname' => 'Test',
        'lname' => 'User',
        'agency' => 'DILG',
        'position' => 'Engineer II',
        'region' => 'Cordillera Administrative Region',
        'province' => 'Benguet',
        'office' => 'Baguio',
        'emailaddress' => 'subaybayancordillera@gmail.com',
        'mobileno' => '09123456789',
        'username' => 'testuser' . time(),
        'password' => bcrypt('password123'),
        'role' => 'user',
        'status' => 'inactive',
        'access' => 'none'
    ]);
    
    echo "✓ User created: " . $user->username . "\n";
    echo "  Email: " . $user->emailaddress . "\n";
    echo "  Status: " . $user->status . "\n\n";
    
    // Trigger registered event
    echo "Triggering Registered event...\n";
    event(new Registered($user));
    echo "✓ Event triggered successfully!\n\n";
    
    // Check if email was logged
    echo "Check your Gmail inbox or check the application logs:\n";
    echo "  Inbox: subaybayancordillera@gmail.com\n";
    echo "  Logs: storage/logs/laravel.log\n\n";
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
}

echo "=== End of Test ===\n\n";
