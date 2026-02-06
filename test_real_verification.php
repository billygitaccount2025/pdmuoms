<?php

require_once 'vendor/autoload.php';

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Creating test user for real verification test\n";

// Create a test user
$testUser = App\Models\User::create([
    'fname' => 'Real',
    'lname' => 'Test',
    'agency' => 'DILG',
    'position' => 'Engineer II',
    'region' => 'Cordillera Administrative Region',
    'province' => 'Benguet',
    'office' => 'Baguio',
    'emailaddress' => 'realtest' . time() . '@example.com',
    'mobileno' => '09123456789',
    'username' => 'realtest' . time(),
    'password' => bcrypt('password123'),
    'role' => 'user',
    'status' => 'inactive',
    'access' => 'none'
]);

echo "Created user ID: " . $testUser->idno . "\n";

// Generate verification token
$token = $testUser->generateVerificationToken();
echo "Generated token: " . $token . "\n";

// Check initial status
echo "Initial status: " . $testUser->status . "\n";
echo "Initial verification_token: " . $testUser->verification_token . "\n";

// Now make a real HTTP request to the verification URL
$verificationUrl = "http://localhost:8000/email/verify/token/" . $token;
echo "\nMaking HTTP request to: " . $verificationUrl . "\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $verificationUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HEADER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);

curl_close($ch);

echo "HTTP Response Code: " . $httpCode . "\n";
echo "Redirect URL: " . $redirectUrl . "\n";

// Check final user status
$user = App\Models\User::find($testUser->idno);
echo "\nFinal status:\n";
echo "Status: " . $user->status . "\n";
echo "Email Verified At: " . $user->email_verified_at . "\n";
echo "Verification Token: " . $user->verification_token . "\n";

// Debug: Check if user was found by token
$debugUser = App\Models\User::where('verification_token', $token)->first();
echo "\nDebug - User found by token: " . ($debugUser ? 'YES' : 'NO') . "\n";
if ($debugUser) {
    echo "Debug - User ID: " . $debugUser->idno . "\n";
    echo "Debug - User status before: " . $debugUser->status . "\n";
    echo "Debug - Has verified email: " . ($debugUser->hasVerifiedEmail() ? 'YES' : 'NO') . "\n";
}

if ($user->status === 'active' && is_null($user->verification_token) && !is_null($user->email_verified_at)) {
    echo "\n✓ Real verification successful!\n";
} else {
    echo "\n✗ Real verification failed!\n";
}

echo "\nTest completed\n";
