<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NivelRoguelike;

class NivelRoguelikeSeeder extends Seeder
{
    public function run(): void
    {
        $niveles = [
            [
                'dificultad' => 'fácil',
                'titulo' => 'Suma Simple',
                'descripcion' => 'Imprime la suma de 10 + 20.',
                'codigo_validador' => '30', // Esto es simplificado, en realidad deberia validar con tests unitarios
                'recompensa_monedas' => 5
            ],
            [
                'dificultad' => 'medio',
                'titulo' => 'Bucle For',
                'descripcion' => 'Imprime los números del 1 al 5 seguidos (12345) usando un bucle.',
                'codigo_validador' => '12345',
                'recompensa_monedas' => 15
            ],
            [
                'dificultad' => 'difícil',
                'titulo' => 'Array Reverse',
                'descripcion' => 'Dado el array [1,2,3], imprímelo al revés: 321.',
                'codigo_validador' => '321',
                'recompensa_monedas' => 30
            ]
        ];

        foreach ($niveles as $nivel) {
            NivelRoguelike::firstOrCreate(['titulo' => $nivel['titulo']], $nivel);
        }
    }
}
