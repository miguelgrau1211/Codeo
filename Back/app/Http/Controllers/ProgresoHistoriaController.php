<?php

namespace App\Http\Controllers;

use App\Models\ProgresoHistoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\NivelesHistoria;

class ProgresoHistoriaController extends Controller
{
    // progreso de un usuario
    // progreso de un usuario
    public function index()
    {
        $progreso = ProgresoHistoria::where('usuario_id', Auth::id())
            ->with('nivel')
            ->get();

        return response()->json($progreso, 200);
    }

    //Guarda o actualiza el progreso de un nivel
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nivel_id'   => 'required|exists:niveles_historia,id',
            'completado' => 'required|boolean',
            'codigo_solucion_usuario' => 'nullable|string'
        ]);

        $userId = Auth::id();
        $nivelId = $validatedData['nivel_id'];

        // Verificar estado anterior para dar recompensa solo la primera vez
        $progresoAnterior = ProgresoHistoria::where('usuario_id', $userId)
            ->where('nivel_id', $nivelId)
            ->first();

        $yaEstabaCompletado = $progresoAnterior && $progresoAnterior->completado;

        //Si ya existe lo actualiza si no lo crea
        $progreso = ProgresoHistoria::updateOrCreate(
            [
                'usuario_id' => $userId,
                'nivel_id'   => $nivelId
            ],
            [
                'completado' => $validatedData['completado'],
                'codigo_solucion_usuario' => $validatedData['codigo_solucion_usuario']
            ]
        );

        $recompensas = [];

        // Lógica de Recompensas (Solo si se completa por primera vez y es exitoso)
        if ($validatedData['completado'] && !$yaEstabaCompletado) {
            /** @var \App\Models\Usuario $user */
            $user = Auth::user();
            
            // Definir recompensas (se podrían sacar del modelo Nivel en el futuro)
            $xpGanada = 100;
            $monedasGanadas = 50;

            $user->increment('exp_total', $xpGanada);
            $user->increment('monedas', $monedasGanadas);

            // Verificar subida de nivel (Ejemplo: cada 1000 XP)
            // $nuevoNivel = floor($user->exp_total / 1000) + 1;
            // if ($nuevoNivel > $user->nivel_global) { ... }

            $recompensas = [
                'xp' => $xpGanada,
                'monedas' => $monedasGanadas,
                'mensaje' => '¡Nivel completado! Ganaste ' . $xpGanada . ' XP y ' . $monedasGanadas . ' monedas.'
            ];
        }

        return response()->json([
            'message' => 'Progreso guardado correctamente',
            'data' => $progreso,
            'recompensas' => $recompensas
        ], 200);
    }

    /**
     * Obtiene el progreso detallado del usuario en el Modo Historia.
     * * @param int $idUsuario
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Obtiene el progreso detallado del usuario en el Modo Historia.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProgresoModoHistoriaUsuario()
    {
        $idUsuario = Auth::id();

        // 1. Obtener TODOS los niveles ordenados
        // Usamos 'with' para cargar el progreso SOLO del usuario actual
        // Esto evita el problema de que si no has empezado, no sales en la lista.
        $niveles = NivelesHistoria::orderBy('orden', 'asc')->get();

        // 2. Obtener el progreso del usuario indexado por nivel_id para acceso rápido
        $progresoUsuario = ProgresoHistoria::where('usuario_id', $idUsuario)
                            ->get()
                            ->keyBy('nivel_id');

        // 3. Mapear niveles + progreso
        $progresoDetallado = $niveles->map(function ($nivel) use ($progresoUsuario) {
            $progreso = $progresoUsuario->get($nivel->id);

            return [
                // Datos del Progreso (si existe)
                'id' => $progreso ? $progreso->id : null, 
                'usuario_id' => $progreso ? $progreso->usuario_id : Auth::id(),
                'nivel_id' => $nivel->id,
                'completado' => $progreso ? $progreso->completado : 0,
                // Fallback: Si no hay código guardado, usar el inicial del nivel
                'codigo_solucion_usuario' => $progreso && $progreso->codigo_solucion_usuario 
                                            ? $progreso->codigo_solucion_usuario 
                                            : $nivel->codigo_inicial,
                'created_at' => $progreso ? $progreso->created_at : null,
                'updated_at' => $progreso ? $progreso->updated_at : null,

                // Datos del Nivel (Eloquent ya ha hecho el cast de test_cases a array)
                'titulo' => $nivel->titulo,
                'orden' => $nivel->orden,
                'codigo_inicial' => $nivel->codigo_inicial,
                'descripcion' => $nivel->descripcion,
                'contenido_teorico' => $nivel->contenido_teorico,
                'test_cases' => $nivel->test_cases // Esto ya es un array gracias al modelo
            ];
        });

        // Estadísticas
        $totalNiveles = $niveles->count();
        $nivelesCompletados = $progresoUsuario->where('completado', 1)->count();
        $porcentaje = $totalNiveles > 0 ? round(($nivelesCompletados / $totalNiveles) * 100) : 0;
        
        // Último nivel jugado (o el primero si no hay nada)
        // Buscamos el último nivel completado + 1, o el primero
        $ultimoNivelJugado = $progresoUsuario->sortByDesc('updated_at')->first();
        $tituloUltimo = $ultimoNivelJugado 
                        ? ($niveles->firstWhere('id', $ultimoNivelJugado->nivel_id)->titulo ?? 'Inicio') 
                        : 'Inicio';

        return response()->json([
            'usuario_id' => $idUsuario,
            'stats' => [
                'total_niveles' => $totalNiveles,
                'completados' => $nivelesCompletados,
                'porcentaje_progreso' => $porcentaje . '%',
                'titulo_ultimo_nivel' => $tituloUltimo
            ],
            'progreso_detallado' => $progresoDetallado
        ], 200);
    }


    public function getPorcentajeUsuarioModoHistoria()
    {
        $idUsuario = Auth::id();

        //obtenemos el progreso con los datos del nivel
        $progreso = ProgresoHistoria::where('usuario_id', $idUsuario)->join('niveles_historia', 'usuario_progreso_historia.nivel_id', '=', 'niveles_historia.id')
            ->select(
                'usuario_progreso_historia.*', 
                'niveles_historia.titulo', 
                'niveles_historia.orden'
            )->orderBy('niveles_historia.orden', 'asc')->get();

        //estadísticas para el Dashboard
        $totalNiveles = NivelesHistoria::count();
        $nivelesCompletados = $progreso->where('completado', true)->count();
        
        //porcentaje
        $porcentajeCerrado = $totalNiveles > 0 ? round(($nivelesCompletados / $totalNiveles) * 100) : 0;

        return response()->json([
            'usuario_id' => (int)$idUsuario,
            'stats' => [
                'total_niveles' => $totalNiveles,
                'completados' => $nivelesCompletados,
                'porcentaje_progreso' => $porcentajeCerrado . '%'
            ],
            'progreso_detallado' => $progreso
        ], 200);
    }
}