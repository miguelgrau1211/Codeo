<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NivelRoguelike;

/**
 * Seeder para los desafíos del Modo Roguelike (Infinito).
 * Todos los desafíos han sido actualizados a Python con recompensas escaladas.
 */
class NivelRoguelikeSeeder extends Seeder
{
    /**
     * Ejecuta el seeder para poblar los niveles del modo Roguelike.
     */
    public function run(): void
    {
        $niveles = [
            // --- FÁCIL (20 Monedas) ---
            [
                'dificultad' => 'fácil',
                'titulo' => 'Suma de Entradas',
                'descripcion' => 'Recibes una lista con dos números [a, b]. Imprime su suma.',
                'test_cases' => [['input' => '[10, 20]', 'output' => '30'], ['input' => '[5, 5]', 'output' => '10']],
                'recompensa_monedas' => 20
            ],
            [
                'dificultad' => 'fácil',
                'titulo' => 'Longitud de Nombre',
                'descripcion' => 'Recibes un string. Imprime cuántos caracteres tiene.',
                'test_cases' => [['input' => '"Python"', 'output' => '6'], ['input' => '""', 'output' => '0']],
                'recompensa_monedas' => 20
            ],
            [
                'dificultad' => 'fácil',
                'titulo' => 'Grito Energético',
                'descripcion' => 'Convierte el string de entrada a mayúsculas.',
                'test_cases' => [['input' => '"hola"', 'output' => 'HOLA'], ['input' => '"code"', 'output' => 'CODE']],
                'recompensa_monedas' => 20
            ],

            // --- MEDIO (60 Monedas) ---
            [
                'dificultad' => 'medio',
                'titulo' => 'Solo Pares',
                'descripcion' => 'Dada una lista de números, imprime una nueva lista conteniendo solo los pares.',
                'test_cases' => [['input' => '[1, 2, 3, 4]', 'output' => '[2, 4]'], ['input' => '[1, 3, 5]', 'output' => '[]']],
                'recompensa_monedas' => 60
            ],
            [
                'dificultad' => 'medio',
                'titulo' => 'Contador de Vocales',
                'descripcion' => 'Cuenta cuántas vocales (a,e,i,o,u) hay en el string dado.',
                'test_cases' => [['input' => '"manzana"', 'output' => '3'], ['input' => '"xyz"', 'output' => '0']],
                'recompensa_monedas' => 60
            ],
            [
                'dificultad' => 'medio',
                'titulo' => 'Invertir String',
                'descripcion' => 'Imprime el string de entrada del revés.',
                'test_cases' => [['input' => '"abc"', 'output' => 'cba'], ['input' => '"radar"', 'output' => 'radar']],
                'recompensa_monedas' => 60
            ],

            // --- DIFÍCIL (150 Monedas) ---
            [
                'dificultad' => 'difícil',
                'titulo' => 'Palíndromo Checker',
                'descripcion' => 'Imprime True si el string es un palíndromo, False si no lo es.',
                'test_cases' => [['input' => '"anita lava la tina"', 'output' => 'True'], ['input' => '"hola"', 'output' => 'False']],
                'recompensa_monedas' => 150
            ],
            [
                'dificultad' => 'difícil',
                'titulo' => 'Sucesión de Fibonacci',
                'descripcion' => 'Imprime el n-ésimo número de la sucesión de Fibonacci.',
                'test_cases' => [['input' => '5', 'output' => '5'], ['input' => '10', 'output' => '55']],
                'recompensa_monedas' => 150
            ],
            [
                'dificultad' => 'difícil',
                'titulo' => 'Anagramas',
                'descripcion' => 'Dados dos strings en una lista [s1, s2], imprime True si son anagramas.',
                'test_cases' => [['input' => '["amor", "roma"]', 'output' => 'True'], ['input' => '["hola", "chau"]', 'output' => 'False']],
                'recompensa_monedas' => 150
            ],

            // --- EXTREMO (300 Monedas) ---
            [
                'dificultad' => 'extremo',
                'titulo' => 'Validador de Paréntesis',
                'descripcion' => 'Determina si una cadena de paréntesis, corchetes y llaves está balanceada.',
                'test_cases' => [['input' => '"([]{})"', 'output' => 'True'], ['input' => '"([)]"', 'output' => 'False']],
                'recompensa_monedas' => 300
            ],
            [
                'dificultad' => 'extremo',
                'titulo' => 'Suma de Dos Objetivos',
                'descripcion' => 'Dada una lista y un objetivo, imprime los índices de los dos números que sumen el objetivo.',
                'test_cases' => [['input' => '([2, 7, 11, 15], 9)', 'output' => '[0, 1]'], ['input' => '([3, 2, 4], 6)', 'output' => '[1, 2]']],
                'recompensa_monedas' => 300
            ],
            [
                'dificultad' => 'extremo',
                'titulo' => 'Frecuencia de Personajes',
                'descripcion' => 'Imprime un diccionario con la frecuencia de cada carácter en el string.',
                'test_cases' => [['input' => '"aba"', 'output' => "{'a': 2, 'b': 1}"], ['input' => '"c"', 'output' => "{'c': 1}"]],
                'recompensa_monedas' => 300
            ],
        ];

        foreach ($niveles as $nivel) {
            NivelRoguelike::updateOrCreate(
                ['titulo' => $nivel['titulo']],
                $nivel
            );
        }
    }
}
