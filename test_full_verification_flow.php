<?php

require_once 'vendor/autoload.php';

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing full email verification flow\n";

// Create a new test user
$testUser = App\Models\User::create([
    'fname' => 'Test',
    'lname' => 'User2',
    'agency' => 'DILG',
    'position' => 'Engineer II',
    'region' => 'Cordillera Administrative Region',
    'province' => 'Benguet',
    'office' => 'Baguio',
    'emailaddress' => 'test' . time() . '@example.com',
    'mobileno' => '09123456789',
    'username' => 'testuser2' . time(),
    'password' => bcrypt('password123'),
    'role' => 'user',
    'status' => 'inactive',
    'access' => 'none'
]);

echo "Created test user: " . $testUser->idno . "\n";
echo "Initial status: " . $testUser->status . "\n";
echo "Verification token: " . $testUser->verification_token . "\n";

// Send verification email
$testUser->sendEmailVerificationNotification();
echo "Verification email sent\n";

// Get the verification URL
$verificationUrl = url('/email/verify/token/' . $testUser->verification_token);
echo "Verification URL: " . $verificationUrl . "\n";

// Simulate clicking the link (call the controller method)
$controller = new \App\Http\Controllers\Auth\VerificationController();
$response = $controller->verifyWithToken($testUser->verification_token);

echo "Verification response: Redirect to " . $response->getTargetUrl() . "\n";

// Check final user status
$user = \App\Models\User::find($testUser->idno);
echo "\nFinal status:\n";
echo "Status: " . $user->status . "\n";
echo "Email Verified At: " . $user->email_verified_at . "\n";
echo "Verification Token: " . $user->verification_token . "\n";

if ($user->status === 'active' && is_null($user->verification_token) && !is_null($user->email_verified_at)) {
    echo "\n✓ Verification successful!\n";
} else {
    echo "\n✗ Verification failed!\n";
}

echo "\nTest completed\n";
