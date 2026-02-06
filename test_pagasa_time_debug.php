<?php

require 'vendor/autoload.php';
require 'app/Helpers/PagasaTimeHelper.php';

use App\Services\PagasaTimeService;
use Carbon\Carbon;

echo "=== PAGASA Time Debug ===\n\n";

echo "System Time: " . Carbon::now('Asia/Manila')->format('M d, Y H:i:s') . "\n";
echo "System Time (UTC): " . Carbon::now('UTC')->format('M d, Y H:i:s') . "\n\n";

echo "Testing PAGASA Time Service:\n";
try {
    $pagasaTime = PagasaTimeService::getCurrentTime();
    echo "PAGASA Time: " . $pagasaTime->format('M d, Y H:i:s') . "\n";
    echo "PAGASA Time (Timezone): " . $pagasaTime->timezone->getName() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nTesting pagasa_time() helper:\n";
try {
    $helperTime = pagasa_time();
    echo "pagasa_time() result: " . $helperTime->format('M d, Y H:i:s') . "\n";
    echo "Timezone: " . $helperTime->timezone->getName() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nChecking app timezone config:\n";
require 'bootstrap/app.php';
echo "config('app.timezone'): " . config('app.timezone') . "\n";
