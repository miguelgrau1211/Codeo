<?php
/**
 * EMERGENCY DB RESTORATION
 * Re-runs the NivelesHistoriaSeeder to fix corrupted (translated) content.
 */

require 'Back/vendor/autoload.php';
$app = require_once 'Back/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Database\Seeders\NivelesHistoriaSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

echo "Restoring levels to Spanish originals...\n";

try {
    $seeder = new NivelesHistoriaSeeder();
    $seeder->run();
    echo "✅ NivelesHistoria restored.\n";

    // Clear translation cache
    Cache::flush();
    echo "✅ Cache cleared.\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
