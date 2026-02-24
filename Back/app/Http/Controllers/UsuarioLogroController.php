<?php

namespace App\Http\Controllers;

use App\Models\UsuarioLogro;
use App\Services\TranslationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Logros;

class UsuarioLogroController extends Controller
{
    // logros conseguidos
    public function index()
    {
        $logros = UsuarioLogro::where('usuario_id', Auth::id())->with('logro')->get();
        return response()->json($logros, 200);
    }

    // Asigna un logro
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'logro_id' => 'required|exists:logros,id',
        ]);

        try {
            $nuevoLogro = UsuarioLogro::create([
                'usuario_id' => Auth::id(),
                'logro_id' => $validatedData['logro_id'],
                'fecha_desbloqueo' => now(),
            ]);

            return response()->json([
                'message' => '¡Logro desbloqueado con éxito!',
                'data' => $nuevoLogro,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'El usuario ya posee este logro o ha ocurrido un error.',
                'error' => $e->getMessage(),
            ], 409);
        }
    }

    // Elimina un logro
    public function destroy(Request $request, $id)
    {
        $request->validate([
            'logro_id' => 'required|exists:logros,id',
        ]);

        UsuarioLogro::where('usuario_id', Auth::id())
            ->where('logro_id', $request->logro_id)
            ->delete();

        return response()->json(['message' => 'Logro revocado correctamente'], 200);
    }

    /**
     * Obtiene los logros conseguidos por el usuario con nombre/descripción traducidos.
     */
    public function getLogrosUsuario(Request $request)
    {
        $idUsuario = Auth::id();
        $locale = TranslationService::resolveLocale($request);

        $logrosConseguidos = UsuarioLogro::where('usuario_id', $idUsuario)
            ->with('logro')
            ->get();

        $translator = app(TranslationService::class);

        // Extraer los modelos Logros para traducción masiva
        $rawLogros = $logrosConseguidos->pluck('logro');
        $translatedLogros = collect($translator->translateCollection($rawLogros, $locale, 'logro'))
            ->keyBy('id');

        $resultado = $logrosConseguidos->map(function ($item) use ($translatedLogros) {
            $logro = $translatedLogros->get($item->logro_id);

            return [
                'logro_id' => $item->logro_id,
                'nombre' => $logro['nombre'],
                'descripcion' => $logro['descripcion'],
                'icono_url' => $item->logro->icono_url,
                'fecha_desbloqueo' => $item->fecha_desbloqueo,
                'requisito_tipo' => $item->logro->requisito_tipo,
            ];
        });

        return response()->json([
            'usuario_id' => (int) $idUsuario,
            'total_logros' => $resultado->count(),
            'logros' => $resultado,
        ], 200);
    }

    /**
     * Obtiene la lista completa de logros indicando cuáles ha desbloqueado el usuario.
     * Nombre y descripción se traducen según Accept-Language.
     * Fix: la fecha de desbloqueo se carga de una sola query (evita N+1).
     */
    public function getLogrosDesbloqueados(Request $request)
    {
        $idUsuario = Auth::id();
        $locale = TranslationService::resolveLocale($request);

        $todosLosLogros = Logros::all();

        // Cargar todos los logros del usuario en una sola query → evita N+1
        $userLogros = UsuarioLogro::where('usuario_id', $idUsuario)
            ->get()
            ->keyBy('logro_id');

        $translator = app(TranslationService::class);
        // Traducción masiva de todos los logros
        $translatedLogros = collect($translator->translateCollection($todosLosLogros, $locale, 'logro'))
            ->keyBy('id');

        $resultado = $todosLosLogros->map(function ($logro) use ($userLogros, $translatedLogros) {
            $desbloqueado = $userLogros->has($logro->id);
            $translated = $translatedLogros->get($logro->id);

            return [
                'id' => $logro->id,
                'nombre' => $translated['nombre'],
                'descripcion' => $translated['descripcion'],
                'icono_url' => $logro->icono_url,
                'rareza' => $logro->rareza,
                'requisito_tipo' => $logro->requisito_tipo,
                'requisito_cantidad' => $logro->requisito_cantidad,
                'desbloqueado' => $desbloqueado,
                'fecha_obtencion' => $desbloqueado
                    ? $userLogros->get($logro->id)?->fecha_desbloqueo
                    : null,
            ];
        });

        return response()->json([
            'usuario_id' => (int) $idUsuario,
            'progreso_logros' => count($userLogros) > 0
                ? count($userLogros) . '/' . $todosLosLogros->count()
                : '0/0',
            'lista_completa' => $resultado,
        ], 200);
    }

    /**
     * Obtiene el porcentaje de completitud de logros del usuario.
     */
    public function getPorcentajeLogros()
    {
        $idUsuario = Auth::id();
        $totalLogros = Logros::count();

        if ($totalLogros === 0) {
            return response()->json(['porcentaje' => 0, 'mensaje' => 'No hay logros configurados'], 200);
        }

        $logrosUsuario = UsuarioLogro::where('usuario_id', $idUsuario)->count();
        $porcentaje = ($logrosUsuario / $totalLogros) * 100;

        return response()->json([
            'usuario_id' => (int) $idUsuario,
            'logros_obtenidos' => $logrosUsuario,
            'total_disponibles' => $totalLogros,
            'porcentaje' => round($porcentaje, 2),
            'texto' => "Has completado $logrosUsuario de $totalLogros logros",
        ], 200);
    }
}
