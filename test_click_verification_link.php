<?php

require_once 'vendor/autoload.php';

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing verification link click simulation\n";

// Find the latest test user
$testUser = DB::table('tbusers')->where('emailaddress', 'subaybayancordillera@gmail.com')->orderBy('idno', 'desc')->first();

if (!$testUser) {
    echo "No test user found\n";
    exit;
}

echo "Test User ID: " . $testUser->idno . "\n";
echo "Verification Token: " . $testUser->verification_token . "\n";
echo "Status: " . $testUser->status . "\n";

// Simulate HTTP request to the verification route
$token = $testUser->verification_token;
echo "\nSimulating GET request to /email/verify/token/$token\n";

// Create a mock request
$request = new Request();
$request->setMethod('GET');
$request->server->set('REQUEST_URI', "/email/verify/token/$token");

// Get the controller
$controller = new \App\Http\Controllers\Auth\VerificationController();

// Call the method directly
try {
    $response = $controller->verifyWithToken($token);
    echo "Response: Redirect to " . $response->getTargetUrl() . "\n";
    echo "Response message: " . session('success') . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Check user status after
$user = \App\Models\User::find($testUser->idno);
echo "\nAfter verification:\n";
echo "Status: " . $user->status . "\n";
echo "Email Verified At: " . $user->email_verified_at . "\n";
echo "Verification Token: " . $user->verification_token . "\n";

echo "\nTest completed\n";
