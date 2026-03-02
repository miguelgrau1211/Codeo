<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$niveles = \App\Models\NivelRoguelike::all();
foreach ($niveles as $nivel) {
    echo "ID: {$nivel->id} | Título: {$nivel->titulo} | Descripción: " . ($nivel->descripcion ?: 'EMPTY') . "\n";
}
