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
use App\Actions\DeactivateUserAction;
use App\Http\Resources\UserResource;
use App\Services\TranslationService;
use App\Actions\CheckAchievementsAction;

/**
 * Controlador para la gestión de usuarios.
 * Refactorizado siguiendo patrones Action, DTO y Resource.
 */
class UserController extends Controller
{
    public function index(Request $request, SearchUsersAction $action): JsonResponse
    {
        $usuarios = $action->execute(
            search: $request->query('search'),
            sortBy: $request->query('sort_by', 'id'),
            sortOrder: $request->query('sort_order', 'desc')
        );

        return response()->json($usuarios);
    }

    public function store(StoreUserRequest $request, CreateUserAction $action): JsonResponse
    {
        $usuario = $action->execute(\App\DTOs\User\UserRegistrationData::fromArray($request->validated()));

        return (new UserResource($usuario))
            ->additional(['message' => 'Usuario creado exitosamente'])
            ->response()
            ->setStatusCode(201);
    }

    public function show($id): UserResource
    {
        return new UserResource(Usuario::findOrFail($id));
    }

    public function update(UpdateUserRequest $request, $id, UpdateUserAction $action): JsonResponse
    {
        $usuario = Usuario::findOrFail($id);
        $usuario = $action->execute($usuario, $request->validated());

        return (new UserResource($usuario))
            ->additional(['message' => 'Usuario actualizado exitosamente'])
            ->response();
    }

    public function updatePropio(UpdateUserRequest $request, UpdateUserAction $action): JsonResponse
    {
        return $this->update($request, Auth::id(), $action);
    }

    public function destroy($id): JsonResponse
    {
        Usuario::findOrFail($id)->delete();
        return response()->json(['message' => 'Usuario eliminado exitosamente']);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $usuario = Usuario::where('email', $credentials['email'])->first();

        if (!$usuario || !Hash::check($credentials['password'], $usuario->password)) {
            return response()->json(['message' => 'Las credenciales proporcionadas son incorrectas.'], 401);
        }

        $token = $usuario->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'nickname' => $usuario->nickname,
            'avatar_url' => $usuario->avatar_url,
            'es_admin' => (bool) $usuario->es_admin
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }

    public function getUserData(Request $request, GetUserSummaryAction $action): JsonResponse
    {
        $summary = $action->execute(Auth::user(), $request);
        return response()->json($summary->toArray());
    }

    public function getActividadUsuarioReciente(GetUserRecentActivityAction $action): JsonResponse
    {
        return response()->json(['actividad' => $action->execute(Auth::user())]);
    }

    public function getRanking(): JsonResponse
    {
        $ranking = Usuario::select('nickname', 'avatar_url', 'nivel_global', 'exp_total')
            ->orderByDesc('exp_total')
            ->paginate(10);

        $items = $ranking->getCollection()->map(function ($usuario, $key) use ($ranking) {
            return [
                'posicion' => (($ranking->currentPage() - 1) * $ranking->perPage()) + $key + 1,
                'nickname' => $usuario->nickname,
                'avatar_url' => $usuario->avatar_url ?? 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . $usuario->nickname,
                'nivel' => $usuario->nivel_global ?? 1,
                'puntos' => $usuario->exp_total ?? 0
            ];
        });

        return response()->json([
            'pagina_actual' => $ranking->currentPage(),
            'total_paginas' => $ranking->lastPage(),
            'usuarios' => $items
        ]);
    }

    public function toggleStatus(Request $request, $id, DeactivateUserAction $action): JsonResponse
    {
        $usuario = Usuario::find($id);

        if ($usuario) {
            if ($usuario->id === Auth::id()) {
                return response()->json(['message' => 'No puedes desactivarte a ti mismo'], 403);
            }

            $motivo = $request->input('motivo', 'Desactivado por el administrador');
            $action->execute($usuario, $motivo);

            AdminLog::create([
                'user_id' => Auth::id(),
                'action' => 'BAN_USER',
                'details' => "Baneó a usuario ID: {$usuario->id} ({$usuario->nickname}) - Motivo: {$motivo}",
            ]);

            return response()->json(['message' => 'Usuario desactivado correctamente', 'estado' => 'desactivado']);
        }

        $archivado = UsuarioDesactivado::where('usuario_id_original', $id)->first();
        if ($archivado) {
            return (new UsuarioDesactivadoController())->reactivar($request, $id);
        }

        return response()->json(['message' => 'Usuario no encontrado'], 404);
    }

    public function esAdmin(): JsonResponse
    {
        return response()->json(['es_admin' => (bool) Auth::user()?->es_admin]);
    }

    public function validateUser(): JsonResponse
    {
        try {
            // Force checking the sanctum guard explicitly
            $user = auth('sanctum')->user();

            if (!$user) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Token no válido o expirado'
                ], 401);
            }

            return response()->json([
                'valid' => true,
                'nickname' => $user->nickname,
                'email' => $user->email,
                'es_admin' => (bool) $user->es_admin
            ]);
        } catch (\Exception $e) {
            \Log::error("Error crítico en validateUser: " . $e->getMessage());
            return response()->json([
                'valid' => false,
                'message' => 'Error de servidor al validar token'
            ], 500);
        }
    }

    public function updatePreferencias(Request $request, UpdateUserAction $action): JsonResponse
    {
        $request->validate(['preferencias' => 'required|array']);
        $action->execute(Auth::user(), ['preferencias' => $request->input('preferencias')]);

        return response()->json(['message' => 'Preferencias guardadas']);
    }

    // Métodos simplificados para información rápida
    public function getMiPosicionRanking(): JsonResponse
    {
        $user = Auth::user();
        return response()->json(['posicion' => Usuario::where('exp_total', '>', $user->exp_total)->count() + 1]);
    }

    public function getFechaDeCreacionCuenta(): JsonResponse
    {
        $user = Auth::user();
        return response()->json([
            'fecha_union' => $user->created_at->format('d/m/Y'),
            'antiguedad' => $user->created_at->diffForHumans()
        ]);
    }
}
