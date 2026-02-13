<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\UsuarioDesactivado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\ProgresoHistoria;
use App\Models\NivelesHistoria;
use App\Models\AdminLog;
use App\Http\Controllers\UsuarioDesactivadoController;
use App\Models\UsuarioLogro;
use App\Models\RunsRoguelike;

class UserController extends Controller
{
    /**
     * Muestra una lista de todos los usuarios.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $sortBy = $request->query('sort_by', 'id');
        $sortOrder = $request->query('sort_order', 'desc');

        // Validar campos de ordenación permitidos
        $allowedSorts = ['id', 'nickname', 'email', 'nivel_global', 'active', 'es_admin'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'id';
        }

        // 1. Consulta Usuarios Activos
        $activos = DB::table('usuarios')
            ->select('id', 'nickname', 'email', 'nivel_global', DB::raw('1 as active'), 'es_admin');

        if ($search) {
            $activos->where(function ($q) use ($search) {
                $q->where('nickname', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // 2. Consulta Usuarios Desactivados
        $desactivados = DB::table('usuarios_desactivados')
            ->select('usuario_id_original as id', 'nickname', 'email', 'nivel_alcanzado as nivel_global', DB::raw('0 as active'), DB::raw('0 as es_admin'));

        if ($search) {
            $desactivados->where(function ($q) use ($search) {
                $q->where('nickname', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // 3. Unión, Ordenación Global y Paginación
        $query = $activos->union($desactivados);

        $usuarios = DB::table(DB::raw("({$query->toSql()}) as combined_users"))
            ->mergeBindings($query)
            ->orderBy($sortBy, $sortOrder)
            ->paginate(8)
            ->withQueryString();

        return response()->json($usuarios, 200);
    }

    /**
     * Almacena un nuevo usuario en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Valida los datos entrantes de la petición basándose en el modelo Usuario
        $validatedData = $request->validate([
            'nickname' => 'required|string|max:50|unique:usuarios',
            'nombre' => 'nullable|string|max:255',
            'apellidos' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios',
            'password' => 'required|string|min:8',
            'avatar_url' => 'nullable|string|url',
            'terminos_aceptados' => 'required|boolean|accepted', // Debe ser true
        ]);

        // Crea una nueva instancia de Usuario con los datos validados
        $usuario = Usuario::create([
            'nickname' => $validatedData['nickname'],
            'nombre' => $validatedData['nombre'] ?? null,
            'apellidos' => $validatedData['apellidos'] ?? null,
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']), // Encriptar contraseña
            'avatar_url' => $validatedData['avatar_url'] ?? null,
            'terminos_aceptados' => $validatedData['terminos_aceptados'],
            // Los campos como monedas, nivel_global, etc., tienen valores por defecto en la BD
        ]);

        // Devuelve el usuario creado y un mensaje de éxito con código 201 (Created)
        return response()->json([
            'message' => 'Usuario creado exitosamente',
            'data' => $usuario
        ], 201);
    }

    /**
     * Muestra un usuario específico.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // Busca el usuario por su ID
        $usuario = Usuario::findOrFail($id);

        // Devuelve los datos del usuario encontrado
        return response()->json($usuario, 200);
    }

    /**
     * Actualiza un usuario existente en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Busca el usuario a actualizar
        $usuario = Usuario::findOrFail($id);

        // Valida los datos entrantes. permitiendo actualización parcial
        $validatedData = $request->validate([
            'nickname' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('usuarios')->ignore($usuario->id),
            ],
            'nombre' => 'nullable|string|max:255',
            'apellidos' => 'nullable|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('usuarios')->ignore($usuario->id),
            ],
            'password' => 'sometimes|required|string|min:8',
            'avatar_url' => 'nullable|string|url',
            'preferencias' => 'nullable|json', // Si se envían preferencias
        ]);

        // Si se envió una contraseña, la encriptamos antes de actualizar
        if (isset($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }

        // Actualiza el usuario con los datos validados
        $usuario->update($validatedData);

        // Devuelve el usuario actualizado y un mensaje
        return response()->json([
            'message' => 'Usuario actualizado exitosamente',
            'data' => $usuario
        ], 200);
    }

    /**
     * Elimina un usuario de la base de datos.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Busca el usuario o falla si no existe
        $usuario = Usuario::findOrFail($id);

        // Elimina el registro de la base de datos
        $usuario->delete();

        // Devuelve mensaje de éxito
        return response()->json([
            'message' => 'Usuario eliminado exitosamente'
        ], 200);
    }
    /**
     * Inicia sesión con un usuario existente.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $usuario = Usuario::where('email', $request->email)->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        // Crear token de acceso (requiere Laravel Sanctum instalado y configurado)
        $token = $usuario->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'nickname' => $usuario->nickname,
            'avatar_url' => $usuario->avatar_url

        ], 200);
    }


    public function esAdmin(Request $request)
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return response()->json(['es_admin' => false], 401);
        }

        return response()->json([
            'es_admin' => (bool) $usuario->es_admin
        ], 200);
    }

    /**
     * Obtiene la experiencia total y el progreso de nivel del usuario
     * * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getExperienciaTotalUsuario()
    {
        //buscamos al usuario autenticado
        $usuario = Usuario::find(Auth::id());

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }


        $xpPorNivel = 1000; //esto se puede cambiar ya que he asignado de momento 
        // que cada nivel da 1000 de xp, pero claro, como no lo hemos hablado 
        // no se cual poner, pero a falta que lo cambiemos para 
        // sacar el nivel del usuario lo dejaré así
        $nivelActual = $usuario->nivel_global;
        $expTotal = $usuario->exp_total;

        // Calcular progreso hacia el siguiente nivel
        $expEnNivelActual = $expTotal % $xpPorNivel;
        $expRestanteParaSubir = $xpPorNivel - $expEnNivelActual;
        $porcentajeNivel = ($expEnNivelActual / $xpPorNivel) * 100;

        return response()->json([
            'id' => $usuario->id,
            'nickname' => $usuario->nickname,
            'exp_total' => $expTotal,
            'nivel_actual' => $nivelActual,
            'detalle_progreso' => [
                'exp_en_este_nivel' => $expEnNivelActual,
                'exp_necesaria_siguiente_nivel' => $xpPorNivel,
                'exp_faltante' => $expRestanteParaSubir,
                'porcentaje_completado' => round($porcentajeNivel, 2) . '%'
            ]
        ], 200);
    }

    /**
     * Obtiene un resumen de la actividad más reciente del usuario (Historia, Logros, Runs).
     * * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActividadUsuarioReciente()
    {
        $id = Auth::id();
        //Valida usuario
        $usuario = Usuario::find($id);
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        //últimos 3 niveles de historia superados
        $historia = \App\Models\ProgresoHistoria::where('usuario_id', $id)
            ->where('completado', true)
            ->with('nivel')
            ->orderBy('updated_at', 'desc')
            ->limit(3)
            ->get()
            ->map(fn($item) => [
                'tipo' => 'Historia',
                'descripcion' => 'Completaste el nivel: ' . $item->nivel->titulo,
                'fecha' => $item->updated_at->diffForHumans() // Ejemplo: "hace 2 horas"
            ]);

        //  últimos 3 logros desbloqueados
        $logros = \App\Models\UsuarioLogro::where('usuario_id', $id)
            ->with('logro')
            ->orderBy('fecha_desbloqueo', 'desc')
            ->limit(3)
            ->get()
            ->map(fn($item) => [
                'tipo' => 'Logro',
                'descripcion' => 'Ganaste el logro: ' . $item->logro->nombre,
                'fecha' => \Carbon\Carbon::parse($item->fecha_desbloqueo)->diffForHumans()
            ]);

        //última run de Roguelike
        $run = \App\Models\RunsRoguelike::where('usuario_id', $id)
            ->orderBy('created_at', 'desc')
            ->limit(1)
            ->get()
            ->map(fn($item) => [
                'tipo' => 'Roguelike',
                'descripcion' => "Llegaste al nivel {$item->niveles_superados} con estado: {$item->estado}",
                'fecha' => $item->created_at->diffForHumans()
            ]);

        // Unir  y ordenar por lo más reciente 
        $actividadGlobal = collect($historia)
            ->merge($logros)
            ->merge($run)
            ->sortByDesc(fn($item) => $item['fecha']) // Aunque diffForHumans es texto, esto es ilustrativo
            ->values();

        return response()->json([
            'usuario' => $usuario->nickname,
            'actividad' => $actividadGlobal
        ], 200);
    }

    /**
     * Obtiene el ranking global de usuarios basado en nivel y experiencia.
     * * @return \Illuminate\Http\JsonResponse
     */
    public function getRanking()
    {
        //usuarios ordenados por nivel (desc) y luego por experiencia (desc)

        $ranking = Usuario::select('id', 'nickname', 'avatar_url', 'nivel_global', 'exp_total')->orderByDesc('nivel_global')->orderByDesc('exp_total')
            // Paginar para no saturar el Frontend si hay muchos  usuarios    
            ->paginate(10); // Devuelve de 10 en 10

        // Transformamos los datos para añadir la posición numérica
        $items = $ranking->getCollection()->map(function ($usuario, $key) use ($ranking) {
            return [
                'posicion' => (($ranking->currentPage() - 1) * $ranking->perPage()) + $key + 1,
                'id' => $usuario->id,
                'nickname' => $usuario->nickname,
                'avatar_url' => $usuario->avatar_url,
                'nivel' => $usuario->nivel_global,
                'puntos' => $usuario->exp_total
            ];
        });

        return response()->json([
            'pagina_actual' => $ranking->currentPage(),
            'total_paginas' => $ranking->lastPage(),
            'usuarios' => $items
        ], 200);
    }

    /**
     * Obtiene la fecha en la que el usuario se registró en Codeo.
     * * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFechaDeCreacionCuenta()
    {
        //buscamos al usuario autenticado
        $usuario = Usuario::findOrFail(Auth::id());

        // formatear  fecha
        return response()->json([
            'id' => $usuario->id,
            'nickname' => $usuario->nickname,
            'fecha_union' => $usuario->created_at->format('d/m/Y'),
            'antiguedad' => $usuario->created_at->diffForHumans()
        ], 200);
    }

    public function validateUser($response)
    {

        $user = Auth::user();

        return response()->json([
            'user' => $user->id,
        ], 200);
    }

    public function getPerfilUsuario()
    {
        return response()->json(Auth::user());
    }

    /**
     * Permite al usuario autenticado actualizar su propio perfil.
     */
    public function updatePropio(Request $request)
    {
        return $this->update($request, Auth::id());
    }

    /**
     * Alterna el estado de un usuario moviéndolo a la tabla de desactivados
     * o restaurándolo desde ella.
     */
    public function toggleStatus(Request $request, $id)
    {
        try {
            // 1. Intentar encontrar al usuario en la tabla principal para desactivarlo
            $usuario = Usuario::find($id);

            if ($usuario) {
                if ($usuario->id === Auth::id()) {
                    return response()->json(['message' => 'No puedes desactivarte a ti mismo'], 403);
                }

                return DB::transaction(function () use ($usuario, $request) {
                    // Mover a la tabla de desactivados
                    UsuarioDesactivado::create([
                        'usuario_id_original' => $usuario->id,
                        'nickname' => $usuario->nickname,
                        'nombre' => $usuario->nombre,
                        'apellidos' => $usuario->apellidos,
                        'email' => $usuario->email,
                        'nivel_alcanzado' => $usuario->nivel_global,
                        'motivo' => $request->input('motivo', 'Desactivado por el administrador'),
                        'fecha_desactivacion' => now(),
                    ]);

                    // Eliminar de la tabla principal
                    $usuario->delete();

                    // log
                    AdminLog::create([
                        'user_id' => Auth::id(),
                        'action' => 'BAN_USER',
                        'details' => "Baneó a usuario ID: {$usuario->id} ({$usuario->nickname}) - Motivo: " . $request->input('motivo', 'Desactivado por el administrador'),
                    ]);

                    return response()->json(['message' => 'Usuario desactivado y archivado correctamente', 'estado' => 'desactivado'], 200);
                });
            }

            // 2. Si no está en 'usuarios', intentar encontrarlo en 'usuarios_desactivados' para reactivarlo
            $archivado = UsuarioDesactivado::where('usuario_id_original', $id)->first();

            if ($archivado) {
                $desactivadoController = new UsuarioDesactivadoController();
                // Reutilizamos la lógica de reactivación que ya tienes
                return $desactivadoController->reactivar($request, $id);
            }

            return response()->json(['message' => 'Usuario no encontrado en ninguna tabla'], 404);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error en toggleStatus: ' . $e->getMessage());
            return response()->json(['message' => 'Error interno del servidor: ' . $e->getMessage()], 500);
        }
    }

    public function getUserData() {
        $id = Auth::id();


        if (!$id) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }
        $usuario = Usuario::find($id);

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }
        $n_achievements = UsuarioLogro::where('usuario_id', $id)->count() ?? 0;
        $total_levels_completed = RunsRoguelike::where('usuario_id', $id)->sum('niveles_superados') + ProgresoHistoria::where('usuario_id', $id)->count() ?? 0;
        $nickname = $usuario->nickname;
        $avatar = $usuario->avatar_url;
        $level = $usuario->nivel_global;
        $experience = $usuario->exp_total;
        $coins = $usuario->monedas;
        $streak = $usuario->streak;
        
        
        return response()->json([
            'nickname' => $nickname,
            'avatar' => $avatar,
            'level' => $level,
            'experience' => $experience,
            'coins' => $coins,
            'streak' => $streak,
            'n_achievements' => $n_achievements,
            'total_levels_completed' => $total_levels_completed
        ], 200);
    }
}
