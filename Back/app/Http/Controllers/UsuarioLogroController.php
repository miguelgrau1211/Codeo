<?php

namespace App\Http\Controllers;

use App\Models\UsuarioLogro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Logros;

class UsuarioLogroController extends Controller
{
    // logros conseguidos
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
            'logro_id'   => 'required|exists:logros,id',
        ]);

        //crear el registro
        try {
            $nuevoLogro = UsuarioLogro::create([
                'usuario_id' => Auth::id(),
                'logro_id'   => $validatedData['logro_id'],
                'fecha_desbloqueo' => now()
            ]);

            return response()->json([
                'message' => '¡Logro desbloqueado con éxito!',
                'data' => $nuevoLogro
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'El usuario ya posee este logro o ha ocurrido un error.',
                'error' => $e->getMessage()
            ], 409);
        }
    }

    // Elimina un logro
    public function destroy(Request $request, $id) // $id es el ID del recurso (logro vinculación) o del logro? Asumimos que es logro_id para simplificar o el id de la relación
    {
        // En este caso, si destruimos por ID de logro para el usuario auth
        // O si destroy recibe el ID de la tabla usuario_logros
        
        // Vamos a asumir que quieres revocar un logro específico. 
        // Si la ruta es apiResource, destroy recibe el ID principal.
        // Pero tu implementación anterior usaba un request body.
        // Adaptamos para usar el parámetro de ruta o request pero validando Auth.
        
        $request->validate([
            'logro_id'   => 'required|exists:logros,id',
        ]);

        UsuarioLogro::where('usuario_id', Auth::id())
            ->where('logro_id', $request->logro_id)
            ->delete();

        return response()->json(['message' => 'Logro revocado correctamente'], 200);
    }
    /**
     * Obtiene todos los logros de un usuario con los detalles del logro.
     * * @param int $idUsuario
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLogrosUsuario()
    {
        $idUsuario = Auth::id();
        
        // obtenemos los logros del usuario
        $logrosConseguidos = UsuarioLogro::where('usuario_id', $idUsuario)
            ->with('logro') 
            ->get();

        // mapeamos los datos para que el objeto 'logro' esté al mismo nivel que la fecha
        $resultado = $logrosConseguidos->map(function ($item) {
            return [
                'logro_id'         => $item->logro_id,
                'nombre'           => $item->logro->nombre,
                'descripcion'      => $item->logro->descripcion,
                'icono_url'        => $item->logro->icono_url,
                'fecha_desbloqueo' => $item->fecha_desbloqueo,
                'requisito_tipo'   => $item->logro->requisito_tipo,
            ];
        });

        return response()->json([
            'usuario_id' => (int)$idUsuario,
            'total_logros' => $resultado->count(),
            'logros' => $resultado
        ], 200);
    }

    /**
     * Obtiene la lista completa de logros del juego, indicando cuáles ha desbloqueado el usuario.
     * * @param int $idUsuario
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLogrosDesbloqueados()
    {
        $idUsuario = Auth::id();
        
        // logros disponibles, todossss
        $todosLosLogros = Logros::all();

        //id de los logros que el usuario ya tiene
        $logrosUsuarioIds = UsuarioLogro::where('usuario_id', $idUsuario)
            ->pluck('logro_id')
            ->toArray();

            

        $resultado = $todosLosLogros->map(function ($logro) use ($logrosUsuarioIds, $idUsuario) {
            $desbloqueado = in_array($logro->id, $logrosUsuarioIds);
            
            return [
                'id' => $logro->id,
                'nombre' => $logro->nombre,
                'descripcion' => $logro->descripcion,
                'icono_url' => $logro->icono_url,
                'requisito_tipo' => $logro->requisito_tipo,
                'requisito_cantidad' => $logro->requisito_cantidad,
                'desbloqueado' => $desbloqueado,//clave para el CSS de Angular
                'fecha_obtencion' => $desbloqueado ? 
                    UsuarioLogro::where('usuario_id', $idUsuario)
                        ->where('logro_id', $logro->id)
                        ->value('fecha_desbloqueo') : null
            ];
        });

        return response()->json([
            'usuario_id' => (int)$idUsuario,
            'progreso_logros' => count($logrosUsuarioIds) > 0 ? count($logrosUsuarioIds) . '/' . $todosLosLogros->count() : '0/0',
            'lista_completa' => $resultado
        ], 200);
    }

    /**
     * Obtiene el porcentaje de completitud de logros de un usuario.
     * * @param int $idUsuario
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPorcentajeLogros()
    {
        $idUsuario = Auth::id();

        //contar cuántos logros existen en total en el juego para luego sacar el procentaje
        $totalLogros = Logros::count();

        if ($totalLogros === 0) {
            return response()->json(['porcentaje' => 0, 'mensaje' => 'No hay logros configurados'], 200);
        }

        // cuántos tiene el usuario
        $logrosUsuario = UsuarioLogro::where('usuario_id', $idUsuario)->count();

        // porcentaje
        $porcentaje = ($logrosUsuario / $totalLogros) * 100;

        return response()->json([
            'usuario_id' => (int)$idUsuario,
            'logros_obtenidos' => $logrosUsuario,
            'total_disponibles' => $totalLogros,
            'porcentaje' => round($porcentaje, 2),
            'texto' => "Has completado $logrosUsuario de $totalLogros logros"
        ], 200);
    }

    
}

