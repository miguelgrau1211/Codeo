<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\UsuarioDesactivado;
use App\Models\AdminLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Actions\User\CreateUserAction;
use App\Actions\User\UpdateUserAction;
use App\Actions\User\GetUserSummaryAction;
use App\Actions\User\GetUserRecentActivityAction;
use App\Actions\User\SearchUsersAction;
use App\Actions\User\GetRankingAction;
use App\Actions\User\LoginUserAction;
use App\Actions\User\DeactivateUserAction;
use App\Actions\User\ReactivateUserAction;
use App\Http\Resources\UserResource;
use App\Services\TranslationService;
use App\Actions\Achievements\CheckAchievementsAction;

/**
 * Controlador para la gestión de usuarios.
 * Refactorizado siguiendo patrones Action, DTO y Resource.
 */
class UserController extends Controller
{
    /**
     * Lista todos los usuarios con filtros de búsqueda y ordenación.
     */
    public function index(Request $request, SearchUsersAction $action): JsonResponse
    {
        $usuarios = $action->execute(
            search: $request->query('search'),
            sortBy: $request->query('sort_by', 'id'),
            sortOrder: $request->query('sort_order', 'desc')
        );

        return response()->json($usuarios);
    }

    /**
     * Registra un nuevo usuario en el sistema.
     */
    public function store(StoreUserRequest $request, CreateUserAction $action): JsonResponse
    {
        $usuario = $action->execute(\App\DTOs\User\UserRegistrationData::fromArray($request->validated()));

        return (new UserResource($usuario))
            ->additional(['message' => 'Usuario creado exitosamente'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Muestra el perfil detallado de un usuario por su ID.
     */
    public function show($id): UserResource
    {
        return new UserResource(Usuario::findOrFail($id));
    }

    /**
     * Actualiza la información de un usuario (Admin).
     */
    public function update(UpdateUserRequest $request, $id, UpdateUserAction $action): JsonResponse
    {
        $usuario = Usuario::findOrFail($id);
        $usuario = $action->execute($usuario, $request->validated());

        return (new UserResource($usuario))
            ->additional(['message' => 'Usuario actualizado exitosamente'])
            ->response();
    }

    /**
     * Actualiza el perfil del usuario autenticado actualmente.
     */
    public function updatePropio(UpdateUserRequest $request, UpdateUserAction $action): JsonResponse
    {
        return $this->update($request, Auth::id(), $action);
    }

    /**
     * Elimina físicamente a un usuario del sistema (Uso administrativo).
     */
    public function destroy($id): JsonResponse
    {
        Usuario::findOrFail($id)->delete();
        return response()->json(['message' => 'Usuario eliminado exitosamente']);
    }

    /**
     * Autentica al usuario y genera un token de acceso (Sanctum).
     */
    public function login(Request $request, LoginUserAction $action): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $result = $action->execute($credentials['email'], $credentials['password']);

        return (new UserResource($result['user']))
            ->additional([
                'message' => 'Inicio de sesión exitoso',
                'access_token' => $result['access_token'],
                'token_type' => $result['token_type'],
            ])
            ->response();
    }

    /**
     * Cierra la sesión activa revocando el token actual.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }

    /**
     * Obtiene un resumen completo de los datos y progreso del usuario para el dashboard.
     */
    public function getUserData(Request $request, GetUserSummaryAction $action): JsonResponse
    {
        $summary = $action->execute(Auth::user(), $request);
        return response()->json($summary->toArray());
    }

    /**
     * Devuelve los últimos eventos o hitos registrados del usuario.
     */
    public function getActividadUsuarioReciente(GetUserRecentActivityAction $action): JsonResponse
    {
        return response()->json(['actividad' => $action->execute(Auth::user())]);
    }

    /**
     * Obtiene la tabla de posiciones con los mejores jugadores.
     */
    public function getRanking(GetRankingAction $action): JsonResponse
    {
        return response()->json($action->execute());
    }

    /**
     * Alterna el estado del usuario: banea/desactiva o reactiva la cuenta.
     */
    public function toggleStatus(Request $request, $id, DeactivateUserAction $deactivateAction, ReactivateUserAction $reactivateAction): JsonResponse
    {
        $usuario = Usuario::find($id);

        if ($usuario) {
            if ($usuario->id === Auth::id()) {
                return response()->json(['message' => 'No puedes desactivarte a ti mismo'], 403);
            }

            $motivo = $request->input('motivo', 'Desactivado por el administrador');
            $deactivateAction->execute($usuario, $motivo);

            AdminLog::create([
                'user_id' => Auth::id(),
                'action' => 'BAN_USER',
                'details' => "Baneó a usuario ID: {$usuario->id} ({$usuario->nickname}) - Motivo: {$motivo}",
            ]);

            return response()->json(['message' => 'Usuario desactivado correctamente', 'estado' => 'desactivado']);
        }

        try {
            $reactivateAction->execute($id, $request->input('password'));
            return response()->json(['message' => 'Usuario reactivado correctamente', 'estado' => 'activo']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    /**
     * Comprueba si el usuario tiene privilegios de administrador.
     */
    public function esAdmin(): JsonResponse
    {
        return response()->json(['es_admin' => (bool) Auth::user()?->es_admin]);
    }

    /**
     * Valida el token actual y devuelve información básica del usuario.
     */
    public function validateUser(): JsonResponse
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            return response()->json(['valid' => false, 'message' => 'Token no válido'], 401);
        }

        return response()->json([
            'valid' => true,
            'nickname' => $user->nickname,
            'email' => $user->email,
            'es_admin' => (bool) $user->es_admin
        ]);
    }

    /**
     * Actualiza la configuración de preferencias personalizada del usuario.
     */
    public function updatePreferencias(Request $request, UpdateUserAction $action): JsonResponse
    {
        $request->validate(['preferencias' => 'required|array']);
        $action->execute(Auth::user(), ['preferencias' => $request->input('preferencias')]);

        return response()->json(['message' => 'Preferencias guardadas']);
    }

    // Métodos simplificados para información rápida
    /**
     * Calcula y devuelve la posición relativa en el ranking global.
     */
    public function getMiPosicionRanking(): JsonResponse
    {
        $user = Auth::user();
        return response()->json(['posicion' => Usuario::where('exp_total', '>', $user->exp_total)->count() + 1]);
    }

    /**
     * Informa sobre la antigüedad de la cuenta del usuario.
     */
    public function getFechaDeCreacionCuenta(): JsonResponse
    {
        $user = Auth::user();
        return response()->json([
            'fecha_union' => $user->created_at->format('d/m/Y'),
            'antiguedad' => $user->created_at->diffForHumans()
        ]);
    }
}
