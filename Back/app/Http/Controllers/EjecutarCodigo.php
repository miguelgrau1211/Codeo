<?php

namespace App\Http\Controllers;

use App\Models\NivelesHistoria;
use App\Models\NivelRoguelike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\RoguelikeSessionController;

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
            'tipo' => 'required|in:historia,roguelike',
            'nivel_id' => 'required|integer',
        ]);

        $codigoUsuario = $request->codigo;
        $tipo = $request->tipo;
        $nivelId = $request->nivel_id;

        // Validar sesión si es Roguelike (Anti-Cheat)
        if ($tipo === 'roguelike') {
            $userId = Auth::id();
            $cacheKey = RoguelikeSessionController::getCacheKey($userId);
            $session = \Illuminate\Support\Facades\Cache::get($cacheKey);

            if (!$session || !$session['level_started_at']) {
                return response()->json([
                    'correcto' => false,
                    'message' => 'No hay una sesión de Roguelike activa o el nivel no ha comenzado.',
                    'game_over' => true
                ], 200);
            }

            $now = now('UTC');
            $startedAt = \Illuminate\Support\Carbon::parse($session['level_started_at'], 'UTC');
            $elapsed = $startedAt->diffInSeconds($now, false);

            $allocatedTime = $session['time_remaining'] ?? 300;

            if ($elapsed > $allocatedTime) {
                return response()->json([
                    'correcto' => false,
                    'message' => '¡Tiempo agotado! No puedes enviar el código fuera de tiempo.',
                    'time_expired' => true
                ], 200);
            }
        }

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
                'code' => $codigoUsuario,
                'tests' => $tests,
            ];

            $response = Http::timeout(30)->post($awsLambdaUrl, $payload);

            if ($response->failed()) {
                Log::error('Lambda request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
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
                    'message' => 'Error de ejecución:',
                    'error' => $lambdaResult['error'],
                ], 200);
            }

            // 3. Procesar resultados — normalizar números flotantes con formato entero (ej: 1.0 → 1)
            // Esto es necesario para que las comparaciones de igualdad con el output esperado sean consistentes.
            $resultadosTests = $lambdaResult['results'] ?? [];

            foreach ($resultadosTests as &$res) {
                if (isset($res['output'])) {
                    $val = $res['output'];
                    // Si el resultado es un número que termina en .0 (ej: "1.0"), se convierte a cadena entera "1"
                    if (is_numeric($val) && floatval($val) == intval($val) && str_contains((string) $val, '.')) {
                        $res['output'] = (string) intval($val);
                    }
                    // Re-evaluar si el test pasa tras la normalización
                    if (isset($res['expected'])) {
                        $res['passed'] = trim((string) $res['output']) === trim((string) $res['expected']);
                    }
                }
            }
            unset($res);

            $todasPasadas = !empty($resultadosTests);

            // Verificar si todos los casos de prueba han superado la validación
            foreach ($resultadosTests as $res) {
                if (isset($res['passed']) && !$res['passed']) {
                    $todasPasadas = false;
                    break;
                }
            }

            // 4. Otorgar recompensas dinámicas (solo activo en modo Roguelike)
            // En el modo historia, las recompensas se gestionan al completar el nivel, no en cada ejecución.
            $recompensas = [];

            if ($todasPasadas && $tipo === 'roguelike') {
                $userId = Auth::id();
                if ($userId) {
                    try {
                        $xp = 25;
                        $coins = 10;

                        // Se usa una transacción para asegurar la integridad de los datos del usuario
                        DB::transaction(function () use ($userId, $xp, $coins) {
                            DB::table('usuarios')
                                ->where('id', $userId)
                                ->lockForUpdate() // Bloqueo de fila para evitar condiciones de carrera en el XP
                                ->increment('exp_total', $xp);

                            DB::table('usuarios')
                                ->where('id', $userId)
                                ->increment('monedas', $coins);
                        });

                        Log::info('Recompensas otorgadas - Roguelike', [
                            'usuario_id' => $userId,
                            'xp' => $xp,
                            'monedas' => $coins,
                        ]);

                        $recompensas = [
                            'xp' => $xp,
                            'monedas' => $coins,
                            'message' => "Ganaste {$xp} XP y {$coins} Monedas.",
                        ];
                    } catch (\Throwable $e) {
                        Log::error('Error otorgando recompensas Roguelike: ' . $e->getMessage());
                    }
                } else {
                    Log::warning('Intento de otorgar recompensas Roguelike sin usuario autenticado.');
                }
            }

            return response()->json([
                'correcto' => $todasPasadas,
                'message' => $todasPasadas
                    ? '¡Genial! Todos los tests pasaron correctamente.'
                    : 'Algunos tests fallaron. Revisa tu lógica e inténtalo de nuevo.',
                'detalles' => $resultadosTests,
                'recompensas' => $recompensas,
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Error en ejecución de código: ' . $e->getMessage());

            return response()->json([
                'correcto' => false,
                'message' => 'Error interno del sistema de evaluación.',
                'detalles' => [],
            ], 200);
        }
    }
}