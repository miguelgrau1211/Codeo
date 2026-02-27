<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NivelesHistoria;

/**
 * Seeder para los niveles del Modo Historia.
 * Ahora los desafíos están centrados en Python.
 */
class NivelesHistoriaSeeder extends Seeder
{
    public function run(): void
    {
        $niveles = [
            [
                'orden' => 1,
                'titulo' => 'Tu primer script en Python',
                'descripcion' => 'Aprende a mostrar mensajes usando la función print().',
                'contenido_teorico' => '<h1>Hola Mundo en Python</h1><p>En Python, usamos <code>print()</code> para mostrar información en la consola.</p><pre>print("Hola Mundo")</pre>',
                'codigo_inicial' => "# En Python no necesitamos etiquetas de apertura\n# Escribe aquí tu primer print\n",
                'test_cases' => [['input' => '', 'output' => 'Hola Mundo']],
                'recompensa_exp' => 100,
                'recompensa_monedas' => 20 // Nivel inicial fácil
            ],
            [
                'orden' => 2,
                'titulo' => 'Variables y Tipos',
                'descripcion' => 'Aprende a declarar variables sin usar símbolos especiales.',
                'contenido_teorico' => '<h1>Variables</h1><p>A diferencia de otros lenguajes, en Python solo asignas un valor a un nombre.</p><pre>nombre = "Python"</pre>',
                'codigo_inicial' => "# Crea una variable llamada 'lenguaje' con el valor 'Python'\n# Luego imprímela\n",
                'test_cases' => [['input' => '', 'output' => 'Python']],
                'recompensa_exp' => 150,
                'recompensa_monedas' => 40
            ],
            [
                'orden' => 3,
                'titulo' => 'Matemáticas con Python',
                'descripcion' => 'Realiza operaciones aritméticas básicas.',
                'contenido_teorico' => '<h1>Operadores</h1><p>Python funciona como una calculadora potente usando +, -, *, /.</p>',
                'codigo_inicial' => "# Imprime el resultado de sumar 15 más 27\n",
                'test_cases' => [['input' => '', 'output' => '42']],
                'recompensa_exp' => 200,
                'recompensa_monedas' => 60
            ],
            [
                'orden' => 4,
                'titulo' => 'Listas (Arrays)',
                'descripcion' => 'Almacena múltiples elementos en una sola variable.',
                'contenido_teorico' => '<h1>Listas</h1><p>Las listas se definen con corchetes <code>[]</code>.</p>',
                'codigo_inicial' => "# Crea una lista con los números 1, 2 y 3\n# Imprime la lista completa\n",
                'test_cases' => [['input' => '', 'output' => '[1, 2, 3]']],
                'recompensa_exp' => 250,
                'recompensa_monedas' => 100
            ],
            [
                'orden' => 5,
                'titulo' => 'Condicionales IF',
                'descripcion' => 'Toma decisiones basadas en condiciones.',
                'contenido_teorico' => '<h1>Control de Flujo</h1><p>Usa <code>if</code> seguido de dos puntos y sangría.</p>',
                'codigo_inicial' => "# x = 10\n# Comprobamos si x es mayor que 5\nif x > 5:\n    # Si es mayor, imprimimos el mensaje\n    print('Es mayor')\n",
                'test_cases' => [['input' => '', 'output' => 'Es mayor']],
                'recompensa_exp' => 300,
                'recompensa_monedas' => 150
            ]
        ];

        foreach ($niveles as $nivel) {
            NivelesHistoria::updateOrCreate(['orden' => $nivel['orden']], $nivel);
        }
    }
}
