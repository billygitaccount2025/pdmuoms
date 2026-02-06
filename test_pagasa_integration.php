<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load Laravel bootstrap
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\PagasaTimeService;

echo "\n=== PAGASA Time Service Test ===\n\n";

try {
    $pagasaTime = PagasaTimeService::getCurrentTime();
    echo "✓ PAGASA Time: " . $pagasaTime->format('Y-m-d H:i:s (l)') . "\n";
    echo "  Timezone: " . $pagasaTime->timezone . "\n\n";
    
    $systemTime = now();
    echo "✓ System Time: " . $systemTime->format('Y-m-d H:i:s (l)') . "\n";
    echo "  Timezone: " . $systemTime->timezone . "\n\n";
    
    echo "✓ Helper functions available:\n";
    echo "  - pagasa_time()\n";
    echo "  - pagasa_adjusted_time()\n";
    echo "  - pagasa_time_offset()\n\n";
    
    echo "=== Test Completed Successfully ===\n";
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "  Stack trace:\n";
    echo $e->getTraceAsString();
}
