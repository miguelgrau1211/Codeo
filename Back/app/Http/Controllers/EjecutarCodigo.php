<?php

namespace App\Http\Controllers;

use App\Models\NivelesHistoria;
use App\Models\NivelRoguelike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class EjecutarCodigo extends Controller
{
    public function ejecutarCodigo(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string',
            'tipo' => 'required|in:historia,roguelike',
            'nivel_id' => 'required|integer'
        ]);

        $codigoUsuario = $request->codigo;
        $tipo = $request->tipo;
        $nivelId = $request->nivel_id;

        // 1. Obtener el resultado esperado de la Base de Datos según el tipo de nivel
        if ($tipo === 'historia') {
            $nivel = NivelesHistoria::find($nivelId);
            if (!$nivel) {
                return response()->json(['message' => 'Nivel de historia no encontrado'], 404);
            }
            $tests = $nivel->test_cases;

        } else { // Roguelike
            $nivel = NivelRoguelike::find($nivelId);
            if (!$nivel) {
                return response()->json(['message' => 'Nivel roguelike no encontrado'], 404);
            }
            $tests = $nivel->test_cases;
        }

        if (!$tests || !is_array($tests)) {
             return response()->json([
                'correcto' => false,
                'message' => 'Error: No hay casos de prueba configurados para este nivel.',
            ], 200);
        }

        // 2. Ejecutar el código del usuario usando AWS Lambda (UNA SOLA LLAMADA)
        $awsLambdaUrl = env('AWS_LAMBDA_URL'); 
        
        try {
            // Enviamos el código y TODOS los tests a la Lambda
            $payload = [
                'code' => $codigoUsuario,
                'tests' => $tests // Array de {input, output}
            ];

            $response = Http::post($awsLambdaUrl, $payload);

            if ($response->failed()) {
                 throw new \Exception("Error al conectar con el servidor de ejecución: " . $response->status());
            }

            $lambdaRaw = $response->json();
            
            // Decodificar el body si la Lambda devuelve formato Proxy (API Gateway)
            if (isset($lambdaRaw['body']) && is_string($lambdaRaw['body'])) {
                $lambdaResult = json_decode($lambdaRaw['body'], true);
            } else {
                $lambdaResult = $lambdaRaw;
            }

            // Verificar si hubo error general en la Lambda (fuera de los tests)
            if (isset($lambdaResult['error']) && !empty($lambdaResult['error'])) {
                return response()->json([
                    'correcto' => false,
                    'message' => 'Error crítico de ejecución:',
                    'error' => $lambdaResult['error']
                ], 200);
            }

            // 3. Procesar resultados devueltos por la Lambda
            // La Lambda debe devolver 'results' => [ {passed, input, expected, actual, output, error}, ... ]
            $resultadosTests = $lambdaResult['results'] ?? [];
            $todasPasadas = true;

            // Verificar si todos pasaron
            foreach ($resultadosTests as $res) {
                 // La lambda devuelve 'passed' (boolean)
                 if (isset($res['passed']) && !$res['passed']) {
                     $todasPasadas = false;
                     break;
                 }
            }
            
            $recompensas = [];

            if ($todasPasadas && $tipo === 'roguelike') {
                /** @var \App\Models\Usuario $user */
                $user = Auth::user();
                if ($user) {
                    $xp = 25;
                    $coins = 10;
                    $user->increment('exp_total', $xp);
                    $user->increment('monedas', $coins);
                    
                    $recompensas = [
                        'xp' => $xp,
                        'monedas' => $coins,
                        'message' => "Ganaste $xp XP y $coins Monedas."
                    ];
                }
            }
            
            // Estructura de respuesta para el Front
            return response()->json([
                'correcto' => $todasPasadas,
                'message' => $todasPasadas ? '¡Genial! Todos los tests pasaron.' : 'Algunos tests fallaron. Revisa tu lógica.',
                'detalles' => $resultadosTests,
                'recompensas' => $recompensas
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error interno del sistema de evaluación',
                'error' => $e->getMessage()
            ], 200); 
        }
    }
}