<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║   SIMULATING: User Clicking Verification Link from Email       ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Get the pending user
$pendingUser = App\Models\User::where('status', 'inactive')->first();

if (!$pendingUser) {
    echo "No users pending verification.\n";
    exit;
}

echo "User to Verify: " . $pendingUser->fname . " " . $pendingUser->lname . "\n";
echo "Email: " . $pendingUser->emailaddress . "\n";
echo "Status BEFORE: " . $pendingUser->status . "\n";
echo "Token: " . substr($pendingUser->verification_token, 0, 30) . "...\n\n";

// Simulate clicking the link
$token = $pendingUser->verification_token;
$url = "http://localhost:8000/email/verify/token/" . $token;

echo "Simulating Click on Verification Link:\n";
echo "URL: " . $url . "\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_VERBOSE, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

echo "HTTP Status: " . $httpCode . "\n";

if (!empty($error)) {
    echo "✗ Error: " . $error . "\n";
} else {
    echo "✓ Request successful\n";
}

echo "\nResponse contains:\n";

if (strpos($response, 'verified successfully') !== false) {
    echo "✓ 'verified successfully' message found\n";
}

if (strpos($response, 'login') !== false) {
    echo "✓ Redirected to login page\n";
}

echo "\n";

// Check the database now
echo "Checking user status AFTER clicking link:\n";
echo "─────────────────────────────────────────────────────────────────\n";

$updatedUser = App\Models\User::find($pendingUser->idno);

echo "Status AFTER: " . $updatedUser->status . "\n";
echo "Email Verified: " . ($updatedUser->hasVerifiedEmail() ? 'YES' : 'NO') . "\n";
echo "Verified At: " . $updatedUser->email_verified_at . "\n";
echo "Token: " . (is_null($updatedUser->verification_token) ? 'CLEARED (NULL)' : 'Still present') . "\n\n";

// Summary
if ($updatedUser->status === 'active' && is_null($updatedUser->verification_token)) {
    echo "✅ VERIFICATION SUCCESSFUL!\n";
    echo "   Status changed: inactive → active\n";
    echo "   Token cleared: YES\n";
    echo "   Email verified: YES\n";
    echo "\n✓ User can now login!\n";
} else {
    echo "❌ VERIFICATION FAILED!\n";
    
    if ($updatedUser->status === 'inactive') {
        echo "   Status is still 'inactive'\n";
        echo "   Controller method may not be running\n";
    }
    
    if (!is_null($updatedUser->verification_token)) {
        echo "   Token still exists in database\n";
        echo "   Update operation may have failed\n";
    }
}

echo "\n";
