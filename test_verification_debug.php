#!/usr/bin/env php
<?php
/**
 * Debug Email Verification Process
 * Run: php test_verification_debug.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "\n=== EMAIL VERIFICATION DEBUG ===\n\n";

// Check database connection
echo "✓ Database Connection\n";
try {
    DB::connection()->getPdo();
    echo "  ✓ Connected to database: " . DB::getDatabaseName() . "\n";
} catch (\Exception $e) {
    echo "  ✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Check for inactive users
echo "\n✓ Checking for inactive users\n";
$inactiveUsers = User::where('status', 'inactive')->get();
echo "  Found " . $inactiveUsers->count() . " inactive users\n";

foreach ($inactiveUsers as $user) {
    echo "  - User ID: {$user->idno}, Email: {$user->emailaddress}, Token: " . ($user->verification_token ? 'Present' : 'Missing') . "\n";
}

// Check for users with verification tokens
echo "\n✓ Checking for users with verification tokens\n";
$usersWithTokens = User::whereNotNull('verification_token')->get();
echo "  Found " . $usersWithTokens->count() . " users with verification tokens\n";

foreach ($usersWithTokens as $user) {
    echo "  - User ID: {$user->idno}, Email: {$user->emailaddress}, Status: {$user->status}\n";
}

// Test verification process
if ($inactiveUsers->count() > 0) {
    echo "\n✓ Testing verification process\n";
    $testUser = $inactiveUsers->first();

    if ($testUser->verification_token) {
        echo "  Testing verification for user: {$testUser->emailaddress}\n";

        // Simulate the verification process
        $token = $testUser->verification_token;
        echo "  Token: " . substr($token, 0, 10) . "...\n";

        // Check if user exists with token
        $foundUser = User::where('verification_token', $token)->first();
        if ($foundUser) {
            echo "  ✓ User found with token\n";
            echo "  Current status: {$foundUser->status}\n";
            echo "  Has verified email: " . ($foundUser->hasVerifiedEmail() ? 'Yes' : 'No') . "\n";

            if (!$foundUser->hasVerifiedEmail()) {
                // Simulate verification
                echo "  Simulating verification...\n";
                $foundUser->markEmailAsVerified();
                $foundUser->update(['status' => 'active', 'verification_token' => null]);

                echo "  ✓ Verification completed\n";
                echo "  New status: {$foundUser->status}\n";
                echo "  Email verified at: {$foundUser->email_verified_at}\n";
            } else {
                echo "  User already verified\n";
            }
        } else {
            echo "  ✗ User not found with token\n";
        }
    } else {
        echo "  ✗ Test user has no verification token\n";
    }
}

echo "\n=== DEBUG COMPLETE ===\n\n";
