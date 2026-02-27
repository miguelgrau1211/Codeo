<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\Usuario::find(1);
if (!$user) {
    echo "User not found\n";
    exit;
}

try {
    $action = new \App\Actions\ProcessPurchaseAction();
    $result = $action->createPaymentIntent($user);
    print_r($result);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
