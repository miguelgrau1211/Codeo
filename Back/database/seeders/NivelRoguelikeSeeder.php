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
                'descripcion' => 'Imprime la suma de dos numeros dados.',
                'test_cases' => '[{"input": "10 20", "output": "30"}, {"input": "20 20", "output": "40"}]',
                'recompensa_monedas' => 5
            ],
            [
                'dificultad' => 'medio',
                'titulo' => 'Bucle For',
                'descripcion' => 'Imprime los números del un nuemro al otro seguidos (12345) usando un bucle.',
                'test_cases' => '[{"input": "1 5", "output": "12345"}, {"input": "2 6", "output": "23456"}]',
                'recompensa_monedas' => 15
            ],
            [
                'dificultad' => 'difícil',
                'titulo' => 'Array Reverse',
                'descripcion' => 'Dado un array, imprímelo al revés.',
                'test_cases' => '[{"input": "[1,2,3]", "output": "321"}, {"input": "[4,5,6]", "output": "654"}]',
                'recompensa_monedas' => 30
            ]
        ];

        foreach ($niveles as $nivel) {
            NivelRoguelike::firstOrCreate(['titulo' => $nivel['titulo']], $nivel);
        }
    }
}
