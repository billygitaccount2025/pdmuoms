<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Http\Request;
use App\Http\Controllers\Auth\LoginController;
use App\Models\User;

// Simulate a login request
$controller = new LoginController();

echo "=== Testing Login Flow ===\n\n";

// Create a mock request
$request = new Request([
    'username' => 'bdferreol',
    'password' => 'Test@1234',
]);

echo "1. Testing credentials method:\n";
$credentials = $controller->credentials($request);
echo "   Credentials: " . json_encode($credentials) . "\n\n";

echo "2. Testing attemptLogin method:\n";
$result = $controller->attemptLogin($request);
echo "   Result: " . ($result ? "✅ SUCCESS" : "❌ FAILED") . "\n\n";

echo "3. Checking if user is authenticated:\n";
$user = \Illuminate\Support\Facades\Auth::user();
echo "   Authenticated user: " . ($user ? $user->fname . " " . $user->lname : "NONE") . "\n";
