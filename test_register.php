<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\Auth\RegisterController;

$request = Request::create('/register', 'POST', [
    'fname' => 'John',
    'lname' => 'Doe',
    'agency' => 'DILG',
    'position' => 'Engineer II',
    'region' => 'Cordillera Administrative Region',
    'province' => 'Benguet',
    'office' => 'Baguio',
    'emailaddress' => 'john.doe@example.com',
    'mobileno' => '09123456789',
    'username' => 'johndoe',
    'password' => 'password123',
    'password_confirmation' => 'password123',
    'role' => 'user',
    'status' => 'active',
    'access' => 'limited'
]);

$controller = new RegisterController();
try {
    $response = $controller->register($request);
    echo "Registration successful\n";
} catch (Exception $e) {
    echo "Registration failed: " . $e->getMessage() . "\n";
}

// Check if user was created
$user = \App\Models\User::where('username', 'johndoe')->first();
if ($user) {
    echo "User created: " . $user->fname . " " . $user->lname . "\n";
} else {
    echo "User not found in database\n";
}
