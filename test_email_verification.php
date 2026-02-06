<?php

require_once 'vendor/autoload.php';

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

/**
 * Test email verification setup
 */

// Test 1: Check User model implements MustVerifyEmail
echo "Test 1: Check User model implements MustVerifyEmail\n";
$user = new App\Models\User();
echo ($user instanceof Illuminate\Contracts\Auth\MustVerifyEmail) ? "✓ Pass\n" : "✗ Fail\n";

// Test 2: Check mail configuration
echo "\nTest 2: Check Mail Configuration\n";
echo "MAIL_MAILER: " . config('mail.default') . "\n";
echo "MAIL_HOST: " . config('mail.mailers.smtp.host') . "\n";
echo "MAIL_PORT: " . config('mail.mailers.smtp.port') . "\n";
echo "MAIL_USERNAME: " . config('mail.mailers.smtp.username') . "\n";
echo "MAIL_FROM_ADDRESS: " . config('mail.from.address') . "\n";
echo "MAIL_FROM_NAME: " . config('mail.from.name') . "\n";

// Test 3: Test sending a verification email
echo "\nTest 3: Creating a test user and sending verification email\n";
$testUser = App\Models\User::create([
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

// Send verification email
$testUser->sendEmailVerificationNotification();
echo "✓ Verification email sent to: " . $testUser->emailaddress . "\n";
echo "User ID: " . $testUser->id . "\n";

// Test 4: Verify the verification URL format
echo "\nTest 4: Check verification link format\n";
$verificationUrl = URL::temporarySignedRoute(
    'verification.verify',
    Carbon::now()->addMinutes(60),
    [
        'id' => $testUser->getKey(),
        'hash' => sha1($testUser->getEmailForVerification()),
    ]
);
echo "Verification URL: " . $verificationUrl . "\n";

echo "\n✓ All tests completed!\n";
