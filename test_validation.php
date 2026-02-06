<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\Auth\RegisterController;

echo "Testing validation errors:\n";

// Test 1: Missing required fields
$request = Request::create('/register', 'POST', [
    // Missing fname, lname, etc.
]);
$controller = new RegisterController();
try {
    $controller->register($request);
} catch (Illuminate\Validation\ValidationException $e) {
    echo "Validation errors for missing fields: " . json_encode($e->errors()) . "\n";
}

// Test 2: Invalid email
$request = Request::create('/register', 'POST', [
    'fname' => 'John',
    'lname' => 'Doe',
    'agency' => 'DILG',
    'position' => 'Engineer II',
    'region' => 'Cordillera Administrative Region',
    'province' => 'Benguet',
    'office' => 'Baguio',
    'emailaddress' => 'invalid-email',
    'mobileno' => '09123456789',
    'username' => 'johndoe2',
    'password' => 'password123',
    'password_confirmation' => 'password123',
]);
try {
    $controller->register($request);
} catch (Illuminate\Validation\ValidationException $e) {
    echo "Validation errors for invalid email: " . json_encode($e->errors()) . "\n";
}

// Test 3: Password mismatch
$request = Request::create('/register', 'POST', [
    'fname' => 'John',
    'lname' => 'Doe',
    'agency' => 'DILG',
    'position' => 'Engineer II',
    'region' => 'Cordillera Administrative Region',
    'province' => 'Benguet',
    'emailaddress' => 'john2.doe@example.com',
    'mobileno' => '09123456789',
    'username' => 'johndoe3',
    'password' => 'password123',
    'password_confirmation' => 'differentpassword',
]);
try {
    $controller->register($request);
} catch (Illuminate\Validation\ValidationException $e) {
    echo "Validation errors for password mismatch: " . json_encode($e->errors()) . "\n";
}

// Test 4: Duplicate email
$request = Request::create('/register', 'POST', [
    'fname' => 'John',
    'lname' => 'Doe',
    'agency' => 'DILG',
    'position' => 'Engineer II',
    'region' => 'Cordillera Administrative Region',
    'province' => 'Benguet',
    'emailaddress' => 'john.doe@example.com', // Duplicate
    'mobileno' => '09123456789',
    'username' => 'johndoe4',
    'password' => 'password123',
    'password_confirmation' => 'password123',
]);
try {
    $controller->register($request);
} catch (Illuminate\Validation\ValidationException $e) {
    echo "Validation errors for duplicate email: " . json_encode($e->errors()) . "\n";
}

echo "Validation testing completed.\n";
