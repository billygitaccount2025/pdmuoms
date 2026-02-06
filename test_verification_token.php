<?php

require_once 'vendor/autoload.php';

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing verification token functionality\n";

// Find the latest test user
$testUser = DB::table('tbusers')->where('emailaddress', 'subaybayancordillera@gmail.com')->orderBy('idno', 'desc')->first();

if (!$testUser) {
    echo "No test user found\n";
    exit;
}

echo "Test User ID: " . $testUser->idno . "\n";
echo "Verification Token: " . $testUser->verification_token . "\n";
echo "Status: " . $testUser->status . "\n";
echo "Email Verified At: " . $testUser->email_verified_at . "\n";

// Test the verification URL
$verificationUrl = url('/email/verify/token/' . $testUser->verification_token);
echo "Verification URL: " . $verificationUrl . "\n";

// Simulate the verification process
echo "\nSimulating verification...\n";

$user = \App\Models\User::find($testUser->idno);

if ($user->verifyEmailWithToken($testUser->verification_token)) {
    echo "✓ Verification successful\n";
    echo "New Status: " . $user->status . "\n";
    echo "Email Verified At: " . $user->email_verified_at . "\n";
    echo "Verification Token: " . $user->verification_token . "\n";
} else {
    echo "✗ Verification failed\n";
}

echo "\nTest completed\n";
