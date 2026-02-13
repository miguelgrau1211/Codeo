<?php

namespace App\Http\Controllers;

use App\Models\NivelesHistoria;
use App\Models\NivelRoguelike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EjecutarCodigo extends Controller
{
    /**
     * Ejecuta el código del usuario contra los test cases del nivel.
     * Usa AWS Lambda para ejecución aislada.
     */
    public function ejecutarCodigo(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:10000',
            'tipo'   => 'required|in:historia,roguelike',
            'nivel_id' => 'required|integer',
        ]);

        $codigoUsuario = $request->codigo;
        $tipo = $request->tipo;
        $nivelId = $request->nivel_id;

        // 1. Obtener los test cases del nivel según el tipo
        $nivel = $tipo === 'historia'
            ? NivelesHistoria::find($nivelId)
            : NivelRoguelike::find($nivelId);

        if (!$nivel) {
            return response()->json([
                'correcto' => false,
                'message' => 'Nivel no encontrado.',
            ], 404);
        }

        $tests = $nivel->test_cases;

        if (!$tests || !is_array($tests) || count($tests) === 0) {
            return response()->json([
                'correcto' => false,
                'message' => 'Error: No hay casos de prueba configurados para este nivel.',
            ], 200);
        }

        // 2. Ejecutar el código del usuario usando AWS Lambda
        $awsLambdaUrl = config('services.aws.lambda_url');

        if (!$awsLambdaUrl) {
            Log::error('AWS_LAMBDA_URL no configurada.');
            return response()->json([
                'correcto' => false,
                'message' => 'Error interno: Servicio de ejecución no disponible.',
            ], 500);
        }

        try {
            $payload = [
                'code'  => $codigoUsuario,
                'tests' => $tests,
            ];

            $response = Http::timeout(30)->post($awsLambdaUrl, $payload);

            if ($response->failed()) {
                Log::error('Lambda request failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                throw new \Exception('Error al conectar con el servidor de ejecución.');
            }

            $lambdaRaw = $response->json();

            // Decodificar el body si la Lambda devuelve formato Proxy (API Gateway)
            $lambdaResult = (isset($lambdaRaw['body']) && is_string($lambdaRaw['body']))
                ? json_decode($lambdaRaw['body'], true)
                : $lambdaRaw;

            // Error general de la Lambda (fuera de los tests)
            if (!empty($lambdaResult['error'])) {
                return response()->json([
                    'correcto' => false,
                    'message'  => 'Error de ejecución:',
                    'error'    => $lambdaResult['error'],
                ], 200);
            }

            // 3. Procesar resultados
            $resultadosTests = $lambdaResult['results'] ?? [];
            $todasPasadas = !empty($resultadosTests);

            foreach ($resultadosTests as $res) {
                if (isset($res['passed']) && !$res['passed']) {
                    $todasPasadas = false;
                    break;
                }
            }

            // 4. Otorgar recompensas (solo roguelike, dentro de transacción)
            $recompensas = [];

            if ($todasPasadas && $tipo === 'roguelike') {
                $userId = Auth::id();
                if ($userId) {
                    try {
                        $xp = 25;
                        $coins = 10;

                        DB::transaction(function () use ($userId, $xp, $coins) {
                            DB::table('usuarios')
                                ->where('id', $userId)
                                ->lockForUpdate()
                                ->increment('exp_total', $xp);

                            DB::table('usuarios')
                                ->where('id', $userId)
                                ->increment('monedas', $coins);
                        });

                        Log::info('Recompensas otorgadas - Roguelike', [
                            'usuario_id' => $userId,
                            'xp'         => $xp,
                            'monedas'    => $coins,
                        ]);

                        $recompensas = [
                            'xp'      => $xp,
                            'monedas' => $coins,
                            'message' => "Ganaste {$xp} XP y {$coins} Monedas.",
                        ];
                    } catch (\Throwable $e) {
                        Log::error('Error otorgando recompensas Roguelike: ' . $e->getMessage());
                    }
                } else {
                    Log::warning('Roguelike rewards: Auth::id() returned null.');
                }
            }

            return response()->json([
                'correcto'    => $todasPasadas,
                'message'     => $todasPasadas
                    ? '¡Genial! Todos los tests pasaron.'
                    : 'Algunos tests fallaron. Revisa tu lógica.',
                'detalles'    => $resultadosTests,
                'recompensas' => $recompensas,
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Error en ejecución de código: ' . $e->getMessage());

            return response()->json([
                'correcto' => false,
                'message'  => 'Error interno del sistema de evaluación.',
                'detalles' => [],
            ], 200);
        }
    }
}