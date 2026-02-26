<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

$u = Usuario::where('email', 'admin@codeo.com')->first();
if ($u) {
    echo "Usuario admin: " . $u->email . "\n";
    echo "Raw password check against '12341234': " . (Hash::check('12341234', $u->password) ? 'true' : 'false') . "\n";
} else {
    echo "No admin user found.\n";
}

$u = Usuario::latest()->first();
echo "Último usuario: " . $u->email . "\n";
echo "Password hash: " . $u->password . "\n";
echo "Needs rehash? " . (Hash::needsRehash($u->password) ? 'true' : 'false') . "\n";
