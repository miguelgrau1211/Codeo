<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UsuarioLogroController;
use App\Http\Controllers\ProgresoHistoriaController;
use App\Http\Controllers\RunsRoguelikeController;
use App\Http\Controllers\NivelesRoguelikeController;
use App\Http\Controllers\MejorasController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// --- Rutas Públicas ---
Route::post('/login', [UserController::class, 'login']);
Route::post('/users', [UserController::class, 'store']); // Registro
Route::get('/ranking', [UserController::class, 'getRanking']);

// Datos del juego (pueden ser públicos para mostrar en landing page)
Route::get('/niveles-roguelike/aleatorio/{dificultad}', [NivelesRoguelikeController::class, 'getNivelModoInfinito']);
Route::apiResource('niveles-roguelike', NivelesRoguelikeController::class)->only(['index', 'show']); 
Route::get('/mejoras/random', [MejorasController::class, 'getTresMejorasRandom']);
Route::apiResource('mejoras', MejorasController::class)->only(['index', 'show']);

// --- Rutas Protegidas (Requieren Token) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // Usuario Autenticado
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Gestión de Usuarios (excepto crear que es registro)
    Route::apiResource('users', UserController::class)->except(['store']);
    Route::get('/users/{id}/experiencia', [UserController::class, 'getExperienciaTotalUsuario']);
    Route::get('/users/{id}/actividad', [UserController::class, 'getActividadUsuarioReciente']);
    Route::get('/users/{id}/antiguedad', [UserController::class, 'getFechaDeCreacionCuenta']);
    Route::get('/users/{id}/perfil', [UserController::class, 'getPerfilUsuario']);

    // Logros
    Route::apiResource('usuario-logros', UsuarioLogroController::class);
    Route::get('/users/{id}/logros', [UsuarioLogroController::class, 'getLogrosUsuario']);
    Route::get('/users/{id}/logros-desbloqueados', [UsuarioLogroController::class, 'getLogrosDesbloqueados']);
    Route::get('/users/{id}/porcentaje-logros', [UsuarioLogroController::class, 'getPorcentajeLogros']);

    // Progreso Historia
    Route::get('/progreso-historia', [ProgresoHistoriaController::class, 'index']);
    Route::post('/progreso-historia', [ProgresoHistoriaController::class, 'store']);
    Route::get('/users/{id}/progreso-historia', [ProgresoHistoriaController::class, 'getProgresoModoHistoriaUsuario']);
    Route::get('/users/{id}/porcentaje-historia', [ProgresoHistoriaController::class, 'getPorcentajeUsuarioModoHistoria']);

    // Roguelike
    Route::apiResource('runs-roguelike', RunsRoguelikeController::class);
    Route::get('/users/{id}/mejor-run', [RunsRoguelikeController::class, 'getNivelMejorRunUsuario']);
    
    // Administración de contenido (Proteger crear/borrar niveles y mejoras)
    Route::apiResource('niveles-roguelike', NivelesRoguelikeController::class)->except(['index', 'show']);
    Route::apiResource('mejoras', MejorasController::class)->except(['index', 'show']);
});
