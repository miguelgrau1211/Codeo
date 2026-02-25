<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Mejoras;

class MejorasSeeder extends Seeder
{
    public function run(): void
    {
        $mejoras = [
            [
                'nombre' => 'INFINITE.UPGRADES.DOUBLE_COIN_NAME',
                'descripcion' => 'INFINITE.UPGRADES.DOUBLE_COIN_DESC',
                'tipo' => 'multiplicador',
                'precio_monedas' => 500
            ],
            [
                'nombre' => 'INFINITE.UPGRADES.EXTRA_LIFE_NAME',
                'descripcion' => 'INFINITE.UPGRADES.EXTRA_LIFE_DESC',
                'tipo' => 'vidas_extra',
                'precio_monedas' => 1000
            ],
            [
                'nombre' => 'INFINITE.UPGRADES.EXTRA_TIME_NAME',
                'descripcion' => 'INFINITE.UPGRADES.EXTRA_TIME_DESC',
                'tipo' => 'tiempo_extra',
                'precio_monedas' => 200
            ]
        ];

        foreach ($mejoras as $mejora) {
            Mejoras::firstOrCreate(['nombre' => $mejora['nombre']], $mejora);
        }
    }
}
