<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\UsuarioDesactivado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsuarioDesactivadoController extends Controller
{
    /**
     * Muestra la lista de todos los usuarios que han sido desactivados.
     */
    public function index()
    {
        $desactivados = UsuarioDesactivado::orderBy('fecha_desactivacion', 'desc')->get();
        return response()->json($desactivados, 200);
    }

    /**
     * Reactiva un usuario y lo devuelve a la tabla principal y lo quita de archivados
     * el password no se guarda en la tabla de desactivados por seguridad 
     * por lo que aquí pediremos una nueva o usaremos una por defecto.
     */
    public function reactivar(Request $request, $idOriginal)
    {
        return DB::transaction(function () use ($idOriginal, $request) {
            
            //buscar
            $archivado = UsuarioDesactivado::where('usuario_id_original', $idOriginal)->first();

            if (!$archivado) {
                return response()->json(['message' => 'No se encontró el registro en archivados'], 404);
            }

            //validar que el email o nickname no hayan sido usados
            $existe = Usuario::where('email', $archivado->email)
                             ->orWhere('nickname', $archivado->nickname)
                             ->exists();
            
            if ($existe) {
                return response()->json(['message' => 'El nickname o email ya están ocupados por un usuario activo'], 409);
            }

            // crear de nuevo el usuario en la tabla principal
            Usuario::create([
                'id' => $archivado->usuario_id_original, 
                'nickname' => $archivado->nickname,
                'email' => $archivado->email,
                'password' => bcrypt($request->input('password', 'Codeo')),//contrasña temporal que luego habra que cambiar
                'nivel_global' => $archivado->nivel_alcanzado,
                'terminos_aceptados' => true
            ]);

            //borrar
            $archivado->delete();

            return response()->json([
                'message' => 'Usuario reactivado con éxito. Se ha asignado una contraseña temporal.'
            ], 200);
        });
    }
}