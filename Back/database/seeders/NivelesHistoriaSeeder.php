<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NivelesHistoria;

class NivelesHistoriaSeeder extends Seeder
{
    public function run(): void
    {
        $niveles = [
            [
                'orden' => 1,
                'titulo' => 'Introducción a PHP',
                'descripcion' => 'Aprende a imprimir tu primer mensaje en pantalla.',
                'contenido_teorico' => '<h1>Hola Mundo en PHP</h1><p>En PHP, utilizamos la instrucción <code>echo</code> para mostrar texto en pantalla.</p><pre>echo "Hola Mundo";</pre>',
                'codigo_inicial' => "<?php\n\n// La variable \$input contiene los datos de entrada\n// Escribe tu código aquí",
                'test_cases' => [['input' => '', 'output' => 'Hola Mundo']],
                'recompensa_exp' => 100,
                'recompensa_monedas' => 10
            ],
            [
                'orden' => 2,
                'titulo' => 'Variables',
                'descripcion' => 'Almacena datos en contenedores llamados variables.',
                'contenido_teorico' => '<h1>Variables</h1><p>Las variables en PHP comienzan con el signo <code>$</code>.</p>',
                'codigo_inicial' => "<?php\n\n// La variable \$input contiene los datos de entrada\n\$nombre = \"Juan\";\necho \$nombre;",
                'test_cases' => [['input' => '', 'output' => 'Juan']],
                'recompensa_exp' => 150,
                'recompensa_monedas' => 20
            ],
             [
                'orden' => 3,
                'titulo' => 'Operaciones Matemáticas',
                'descripcion' => 'Realiza sumas y restas básicas.',
                'contenido_teorico' => '<h1>Operadores</h1><p>Puedes sumar (+) y restar (-) números fácilmente.</p>',
                'codigo_inicial' => "<?php\n\n// La variable \$input contiene los datos de entrada\necho 5 + 5;",
                'test_cases' => [['input' => '', 'output' => '10']],
                'recompensa_exp' => 200,
                'recompensa_monedas' => 25
            ]
        ];

        foreach ($niveles as $nivel) {
            NivelesHistoria::updateOrCreate(['orden' => $nivel['orden']], $nivel);
        }
    }
}
