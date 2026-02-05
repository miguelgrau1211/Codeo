<?php

namespace App\Http\Controllers;

use App\Models\NivelesHistoria;
use App\Models\NivelRoguelike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
            // En historia, comparamos con 'solucion_esperada' (asumiendo que es el valor de retorno).
            // OJO: 'solucion_esperada' en la BD podría ser el código que da la solución o el valor exacto.
            // Asumiremos que es el valor exacto que debe devolver el código.
            $tests_historia = $nivel->test_cases;

        } else { // Roguelike
            $nivel = NivelRoguelike::find($nivelId);
            if (!$nivel) {
                return response()->json(['message' => 'Nivel roguelike no encontrado'], 404);
            }
            // En Roguelike, tenemos 'codigo_validador' que podría ser el resultado esperado
            $tests_roguelike = $nivel->test_cases;
        }

        // 2. Ejecutar el código del usuario usando AWS Lambda
        $awsLambdaUrl = env('AWS_LAMBDA_URL'); 
        $todasPasadas = true;
        $detallesTests = [];

        // Determinar qué tests usar
        $tests = isset($tests_historia) ? $tests_historia : $tests_roguelike;

        if (!$tests || !is_array($tests)) {
             return response()->json([
                'correcto' => false,
                'message' => 'Error: No hay casos de prueba configurados para este nivel.',
            ], 200);
        }

        try {
            foreach ($tests as $index => $test) {
                // Preparar petición a Lambda
                $response = Http::post($awsLambdaUrl, [
                    'code' => $codigoUsuario,
                    'input' => $test['input'] ?? '' // Enviar input si existe, o vacío
             // Enviar output si existe, o vacío
                ]);

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

                    // --- DEBUG TEMPORAL REMOVED ---
                    
                    // Procesar respuesta de Lambda
                    if (isset($lambdaResult['error']) && !empty($lambdaResult['error'])) {
                        // Si hubo error de ejecución (SyntaxError, etc)
                        return response()->json([
                            'correcto' => false,
                            'message' => 'Error de ejecución en tu código:',
                            'error' => $lambdaResult['error']
                        ], 200);
                    }

                    $outputObtenido = isset($lambdaResult['output']) ? trim($lambdaResult['output']) : '';
                    $outputEsperado = trim($test['output']);

                    // Comparar (case-insensitive para strings simples, o estricto)
                    // Aquí hacemos comparación estricta de string trimado
                    $pasoTest = ($outputObtenido === $outputEsperado);

                    $detallesTests[] = [
                        'input' => $test['input'] ?? 'Sin input',
                        'esperado' => $outputEsperado,
                        'obtenido' => $outputObtenido,
                        'correcto' => $pasoTest
                    ];

                    if (!$pasoTest) {
                        $todasPasadas = false;
                        // Opcional: break; si quieres parar al primer fallo
                    }
                } // This brace closes the foreach loop
            
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error interno del sistema de evaluación',
                'error' => $e->getMessage()
            ], 200); 
        }

        // 3. Devolver resultado final
        if ($todasPasadas) {
            return response()->json([
                'correcto' => true,
                'message' => '¡Genial! Todos los tests pasaron.',
                'detalles' => $detallesTests
            ], 200);
        } else {
            return response()->json([
                'correcto' => false,
                'message' => 'Algunos tests fallaron. Revisa tu lógica.',
                'detalles' => $detallesTests
            ], 200);
        }
    }
}