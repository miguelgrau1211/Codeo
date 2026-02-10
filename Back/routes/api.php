<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UsuarioLogroController;
use App\Http\Controllers\ProgresoHistoriaController;
use App\Http\Controllers\RunsRoguelikeController;
use App\Http\Controllers\NivelesRoguelikeController;
use App\Http\Controllers\MejorasController;
use App\Http\Controllers\EjecutarCodigo;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\NivelesHistoriaController;

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
Route::get('/niveles-roguelike/aleatorio', [NivelesRoguelikeController::class, 'getNivelModoInfinito']);
Route::apiResource('niveles-roguelike', NivelesRoguelikeController::class)->only(['index', 'show']);
Route::get('/mejoras/random', [MejorasController::class, 'getTresMejorasRandom']);
Route::apiResource('mejoras', MejorasController::class)->only(['index', 'show']);

// --- Rutas Protegidas (Requieren Token) ---
Route::middleware('auth:sanctum')->group(function () {

    // Usuario Autenticado
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Check Admin Role
    Route::get('/es-admin', [UserController::class, 'esAdmin']);

    //validar usuario
    Route::get('/validate-user', [UserController::class, 'validateUser']);

    // Perfil y Actualización Propia
    Route::get('/users/perfil', [UserController::class, 'getPerfilUsuario']);
    Route::put('/users/perfil', [UserController::class, 'updatePropio']); // Método a crear para seguridad
    Route::get('/users/experiencia', [UserController::class, 'getExperienciaTotalUsuario']);
    Route::get('/users/actividad', [UserController::class, 'getActividadUsuarioReciente']);
    Route::get('/users/antiguedad', [UserController::class, 'getFechaDeCreacionCuenta']);

    // Logros
    Route::apiResource('usuario-logros', UsuarioLogroController::class);
    Route::get('/users/logros', [UsuarioLogroController::class, 'getLogrosUsuario']);
    Route::get('/users/logros-desbloqueados', [UsuarioLogroController::class, 'getLogrosDesbloqueados']);
    Route::get('/users/porcentaje-logros', [UsuarioLogroController::class, 'getPorcentajeLogros']);

    // Progreso Historia
    Route::get('/progreso-historia', [ProgresoHistoriaController::class, 'index']);
    Route::post('/progreso-historia', [ProgresoHistoriaController::class, 'store']);
    Route::get('/users/progreso-historia', [ProgresoHistoriaController::class, 'getProgresoModoHistoriaUsuario']);
    Route::get('/users/porcentaje-historia', [ProgresoHistoriaController::class, 'getPorcentajeUsuarioModoHistoria']);

    // Roguelike
    Route::apiResource('runs-roguelike', RunsRoguelikeController::class);
    Route::get('/users/mejor-run', [RunsRoguelikeController::class, 'getNivelMejorRunUsuario']);


    //ejecutar codigo
    Route::post('/ejecutar-codigo', [EjecutarCodigo::class, 'ejecutarCodigo']);

    // Administración de contenido y usuarios (Solo Admin)
    Route::middleware('admin')->group(function () {
        // Admin Routes for Roguelike Levels
        Route::get('/admin/niveles-roguelike', [NivelesRoguelikeController::class, 'indexAdmin']);
        Route::post('/admin/niveles-roguelike', [NivelesRoguelikeController::class, 'store']);
        Route::put('/admin/niveles-roguelike/{id}', [NivelesRoguelikeController::class, 'update']);
        Route::delete('/admin/niveles-roguelike/{id}', [NivelesRoguelikeController::class, 'destroy']);
        Route::get('/admin/niveles-roguelike/{id}', [NivelesRoguelikeController::class, 'show']); // Ensure we can get single level details

        Route::apiResource('mejoras', MejorasController::class)->except(['index', 'show']);

        // Gestión de Usuarios Completa
        Route::get('/admin/users', [UserController::class, 'index']);
        Route::delete('/admin/users/{id}', [UserController::class, 'destroy']);
        Route::post('/admin/users/{id}/toggle-status', [UserController::class, 'toggleStatus']);

        // Gestión de Niveles Historia
        Route::get('/admin/niveles-historia', [NivelesHistoriaController::class, 'indexAdmin']); // Listar todos paginados
        Route::post('/admin/niveles-historia', [NivelesHistoriaController::class, 'store']);
        Route::put('/admin/niveles-historia/{id}', [NivelesHistoriaController::class, 'update']);
        Route::delete('/admin/niveles-historia/{id}', [NivelesHistoriaController::class, 'destroy']);
        Route::get('/admin/niveles-historia/{id}', [NivelesHistoriaController::class, 'show']); // Add show explicit just in case

        Route::get('/admin/niveles-historia/desactivados', [NivelesHistoriaController::class, 'desactivados']);
        Route::post('/admin/niveles-historia/{id}/toggle-status', [NivelesHistoriaController::class, 'toggleStatus']);

        // Gestión de Niveles Roguelike (Desactivados y Toggle)
        Route::get('/admin/niveles-roguelike/desactivados', [NivelesRoguelikeController::class, 'desactivados']);
        Route::post('/admin/niveles-roguelike/{id}/toggle-status', [NivelesRoguelikeController::class, 'toggleStatus']);

        // Dashboard Stats y Logs
        Route::get('/admin/stats', [AdminDashboardController::class, 'getStats']);
        Route::get('/admin/logs', [AdminDashboardController::class, 'getLogs']);
    });
});
