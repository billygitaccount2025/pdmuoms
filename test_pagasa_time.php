<?php

use App\Services\PagasaTimeService;

// Test the PAGASA Time Service
echo "Testing PAGASA Time Service...\n";
echo "================================\n\n";

// Test 1: Get current time from PAGASA
echo "1. Current PAGASA Time:\n";
$pagasaTime = PagasaTimeService::getCurrentTime();
echo "   " . $pagasaTime->format('Y-m-d H:i:s') . "\n";
echo "   Timezone: " . $pagasaTime->timezone . "\n\n";

// Test 2: System time
echo "2. System Time:\n";
$systemTime = now();
echo "   " . $systemTime->format('Y-m-d H:i:s') . "\n";
echo "   Timezone: " . $systemTime->timezone . "\n\n";

// Test 3: Helper functions
echo "3. Using Helper Functions:\n";
echo "   pagasa_time(): " . pagasa_time()->format('Y-m-d H:i:s') . "\n";
echo "   pagasa_adjusted_time(): " . pagasa_adjusted_time()->format('Y-m-d H:i:s') . "\n";
echo "   pagasa_time_offset(): " . pagasa_time_offset() . " seconds\n\n";

echo "================================\n";
echo "Test completed successfully!\n";
