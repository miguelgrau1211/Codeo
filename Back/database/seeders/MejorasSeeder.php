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
                'nombre' => 'Doble Moneda',
                'descripcion' => 'Ganas el doble de monedas en el modo Roguelike.',
                'tipo' => 'multiplicador',
                'precio_monedas' => 500
            ],
            [
                'nombre' => 'Vida Extra',
                'descripcion' => 'Empiezas con una vida extra en cada run.',
                'tipo' => 'vidas_extra',
                'precio_monedas' => 1000
            ],
             [
                'nombre' => 'Tiempo Extra',
                'descripcion' => 'Añade 30 segundos al temporizador del nivel.',
                'tipo' => 'tiempo_extra',
                'precio_monedas' => 200
            ]
        ];

        foreach ($mejoras as $mejora) {
            Mejoras::firstOrCreate(['nombre' => $mejora['nombre']], $mejora);
        }
    }
}
