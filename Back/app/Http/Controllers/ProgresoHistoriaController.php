<?php

namespace App\Http\Controllers;

use App\Models\ProgresoHistoria;
use App\Models\NivelesHistoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProgresoHistoriaController extends Controller
{
    /**
     * Lista el progreso del usuario autenticado con datos del nivel.
     */
    public function index()
    {
        $progreso = ProgresoHistoria::where('usuario_id', Auth::id())
            ->with('nivel')
            ->get();

        return response()->json($progreso, 200);
    }

    /**
     * Guarda o actualiza el progreso de un nivel.
     * Otorga recompensas solo la primera vez que se completa.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nivel_id'                => 'required|exists:niveles_historia,id',
            'completado'              => 'required|boolean',
            'codigo_solucion_usuario' => 'nullable|string|max:10000',
        ]);

        $userId = Auth::id();
        $nivelId = $validatedData['nivel_id'];

        // Verificar estado anterior para dar recompensa solo la primera vez
        $progresoAnterior = ProgresoHistoria::where('usuario_id', $userId)
            ->where('nivel_id', $nivelId)
            ->first();

        $yaEstabaCompletado = $progresoAnterior && $progresoAnterior->completado;

        // Crear o actualizar progreso
        $progreso = ProgresoHistoria::updateOrCreate(
            [
                'usuario_id' => $userId,
                'nivel_id'   => $nivelId,
            ],
            [
                'completado'              => $validatedData['completado'],
                'codigo_solucion_usuario' => $validatedData['codigo_solucion_usuario'],
            ]
        );

        $recompensas = [];

        // Recompensas: solo si se completa por primera vez
        if ($validatedData['completado'] && !$yaEstabaCompletado) {
            try {
                $xpGanada = 100;
                $monedasGanadas = 50;

                DB::transaction(function () use ($userId, $xpGanada, $monedasGanadas) {
                    DB::table('usuarios')
                        ->where('id', $userId)
                        ->lockForUpdate()
                        ->increment('exp_total', $xpGanada);

                    DB::table('usuarios')
                        ->where('id', $userId)
                        ->increment('monedas', $monedasGanadas);
                });

                Log::info('Recompensas otorgadas - Historia', [
                    'usuario_id' => $userId,
                    'nivel_id'   => $nivelId,
                    'xp'         => $xpGanada,
                    'monedas'    => $monedasGanadas,
                ]);

                $recompensas = [
                    'xp'      => $xpGanada,
                    'monedas' => $monedasGanadas,
                    'mensaje' => "¡Nivel completado! Ganaste {$xpGanada} XP y {$monedasGanadas} monedas.",
                ];
            } catch (\Throwable $e) {
                Log::error('Error otorgando recompensas Historia: ' . $e->getMessage());
            }
        }

        return response()->json([
            'message'     => 'Progreso guardado correctamente',
            'data'        => $progreso,
            'recompensas' => $recompensas,
        ], 200);
    }

    /**
     * Obtiene el progreso detallado del usuario en el Modo Historia.
     * Incluye todos los niveles con su estado de progreso.
     */
    public function getProgresoModoHistoriaUsuario()
    {
        $idUsuario = Auth::id();

        // Todos los niveles ordenados
        $niveles = NivelesHistoria::orderBy('orden', 'asc')->get();

        // Progreso del usuario indexado por nivel_id
        $progresoUsuario = ProgresoHistoria::where('usuario_id', $idUsuario)
            ->get()
            ->keyBy('nivel_id');

        // Mapear niveles + progreso
        $progresoDetallado = $niveles->map(function ($nivel) use ($progresoUsuario, $idUsuario) {
            $progreso = $progresoUsuario->get($nivel->id);

            return [
                'id'                      => $progreso?->id,
                'usuario_id'              => $progreso?->usuario_id ?? $idUsuario,
                'nivel_id'                => $nivel->id,
                'completado'              => $progreso?->completado ?? 0,
                'codigo_solucion_usuario' => $progreso?->codigo_solucion_usuario ?? $nivel->codigo_inicial,
                'created_at'              => $progreso?->created_at,
                'updated_at'              => $progreso?->updated_at,

                // Datos del Nivel
                'titulo'             => $nivel->titulo,
                'orden'              => $nivel->orden,
                'codigo_inicial'     => $nivel->codigo_inicial,
                'descripcion'        => $nivel->descripcion,
                'contenido_teorico'  => $nivel->contenido_teorico,
                'test_cases'         => $nivel->test_cases,
            ];
        });

        // Estadísticas
        $totalNiveles = $niveles->count();
        $nivelesCompletados = $progresoUsuario->where('completado', 1)->count();
        $porcentaje = $totalNiveles > 0
            ? round(($nivelesCompletados / $totalNiveles) * 100)
            : 0;

        $ultimoNivelJugado = $progresoUsuario->sortByDesc('updated_at')->first();
        $tituloUltimo = $ultimoNivelJugado
            ? ($niveles->firstWhere('id', $ultimoNivelJugado->nivel_id)?->titulo ?? 'Inicio')
            : 'Inicio';

        return response()->json([
            'usuario_id'        => $idUsuario,
            'stats'             => [
                'total_niveles'       => $totalNiveles,
                'completados'         => $nivelesCompletados,
                'porcentaje_progreso' => $porcentaje . '%',
                'titulo_ultimo_nivel' => $tituloUltimo,
            ],
            'progreso_detallado' => $progresoDetallado,
        ], 200);
    }

    /**
     * Obtiene el porcentaje de progreso del usuario en Modo Historia.
     * Versión ligera para el Dashboard.
     */
    public function getPorcentajeUsuarioModoHistoria()
    {
        $idUsuario = Auth::id();

        $progreso = ProgresoHistoria::where('usuario_id', $idUsuario)
            ->join('niveles_historia', 'usuario_progreso_historia.nivel_id', '=', 'niveles_historia.id')
            ->select(
                'usuario_progreso_historia.*',
                'niveles_historia.titulo',
                'niveles_historia.orden'
            )
            ->orderBy('niveles_historia.orden', 'asc')
            ->get();

        $totalNiveles = NivelesHistoria::count();
        $nivelesCompletados = $progreso->where('completado', true)->count();
        $porcentaje = $totalNiveles > 0
            ? round(($nivelesCompletados / $totalNiveles) * 100)
            : 0;

        return response()->json([
            'usuario_id' => (int) $idUsuario,
            'stats'      => [
                'total_niveles'       => $totalNiveles,
                'completados'         => $nivelesCompletados,
                'porcentaje_progreso' => $porcentaje . '%',
            ],
            'progreso_detallado' => $progreso,
        ], 200);
    }
}