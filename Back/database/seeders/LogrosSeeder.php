<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Logros;

class LogrosSeeder extends Seeder
{
    public function run(): void
    {
        $logros = [
            [
                'nombre' => '¡Hola Mundo!',
                'descripcion' => 'Completa el primer nivel del modo historia.',
                'icono_url' => 'https://cdn-icons-png.flaticon.com/512/1006/1006363.png',
                'requisito_tipo' => 'nivel_historia',
                'requisito_cantidad' => 1
            ],
            [
                'nombre' => 'Ahorrador Compulsivo',
                'descripcion' => 'Acumula un total de 1000 monedas.',
                'icono_url' => 'https://cdn-icons-png.flaticon.com/512/2953/2953363.png',
                'requisito_tipo' => 'monedas',
                'requisito_cantidad' => 1000
            ],
            [
                'nombre' => 'Veterano de Guerra',
                'descripcion' => 'Juega 50 partidas en modo Roguelike.',
                'icono_url' => 'https://cdn-icons-png.flaticon.com/512/5725/5725064.png',
                'requisito_tipo' => 'partidas_roguelike',
                'requisito_cantidad' => 50
            ],
             [
                'nombre' => 'Maestro del Código',
                'descripcion' => 'Alcanza el nivel global 20.',
                'icono_url' => 'https://cdn-icons-png.flaticon.com/512/2038/2038022.png',
                'requisito_tipo' => 'nivel_global',
                'requisito_cantidad' => 20
            ]
        ];

        foreach ($logros as $logro) {
            Logros::firstOrCreate(['nombre' => $logro['nombre']], $logro);
        }
    }
}
