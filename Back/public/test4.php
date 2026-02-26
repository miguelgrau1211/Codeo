<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

$rawPassword = 'createdpassword';
$hashedManually = Hash::make($rawPassword);

$u = Usuario::create([
    'nickname' => Str::random(10),
    'email' => Str::random(10).'@test.com',
    'password' => $hashedManually,
    'terminos_aceptados' => true
]);

echo "Raw password: " . $rawPassword . "\n";
echo "Manually hashed string passed to create: " . $hashedManually . "\n";
echo "Saved in DB: " . $u->password . "\n";
echo "Can be checked with raw? " . (Hash::check($rawPassword, $u->password) ? 'YES' : 'NO') . "\n";

$u2 = Usuario::create([
    'nickname' => Str::random(10),
    'email' => Str::random(10).'@test2.com',
    'password' => $rawPassword, // passing it RAW
    'terminos_aceptados' => true
]);

echo "Raw password passed to create: " . $rawPassword . "\n";
echo "Saved in DB: " . $u2->password . "\n";
echo "Can be checked with raw? " . (Hash::check($rawPassword, $u2->password) ? 'YES' : 'NO') . "\n";
