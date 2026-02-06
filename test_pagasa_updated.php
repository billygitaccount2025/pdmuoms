<?php

use App\Services\PagasaTimeService;
use Carbon\Carbon;

require 'bootstrap/app.php';

echo "=== Testing Updated PAGASA Time Service ===\n\n";

echo "System Time (Asia/Manila): " . Carbon::now('Asia/Manila')->format('Y-m-d H:i:s') . "\n";

try {
    $pagasaTime = PagasaTimeService::getCurrentTime();
    echo "PAGASA Time: " . $pagasaTime->format('Y-m-d H:i:s') . "\n";
    echo "PAGASA Timezone: " . $pagasaTime->timezone->getName() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Testing pagasa_time() helper ===\n";
try {
    // Load the helper
    require 'app/Helpers/PagasaTimeHelper.php';
    
    $helperTime = pagasa_time();
    echo "pagasa_time() result: " . $helperTime->format('Y-m-d H:i:s') . "\n";
    echo "Timezone: " . $helperTime->timezone->getName() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Done ===\n";
