<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

$rawPassword = 'mysecretpassword';
$hashedManually = Hash::make($rawPassword);

$u = new Usuario();
$u->nickname = Str::random(10);
$u->email = Str::random(10).'@test.com';
$u->password = $hashedManually;
$u->terminos_aceptados = true;
$u->save();

echo "Raw password: " . $rawPassword . "\n";
echo "Manually hashed: " . $hashedManually . "\n";
echo "Saved in DB: " . $u->password . "\n";
echo "Can be checked with raw? " . (Hash::check($rawPassword, $u->password) ? 'YES' : 'NO') . "\n";

