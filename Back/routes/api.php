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
use App\Http\Controllers\RoguelikeSessionController;
use App\Http\Controllers\Api\TemaController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\PurchaseController;

/*
|--------------------------------------------------------------------------
| Rutas de la API
|--------------------------------------------------------------------------
|
| Aquí es donde puedes registrar las rutas de la API para tu aplicación.
| Estas rutas son cargadas por el RouteServiceProvider dentro de un grupo que
| tiene asignado el grupo de middleware "api". ¡Disfruta construyendo tu API!
|
*/

// --- Rutas Públicas ---
Route::post('/login', [UserController::class, 'login'])->name('login');
Route::post('/users', [UserController::class, 'store']); // Registro
Route::get('/ranking', [UserController::class, 'getRanking']);

// Google Auth
Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);

// Datos del juego (pueden ser públicos para mostrar en landing page)

Route::apiResource('niveles-roguelike', NivelesRoguelikeController::class)->only(['index']);
Route::get('/mejoras/random', [MejorasController::class, 'getTresMejorasRandom']);
Route::apiResource('mejoras', MejorasController::class)->only(['index', 'show']);

// --- Rutas Protegidas (Requieren Token) ---
Route::middleware('auth:sanctum')->group(function () {

    // Usuario Autenticado
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Verificar Rol de Administrador
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
    Route::post('/users/logros/easter-egg', [UsuarioLogroController::class, 'unlockEasterEgg']);

    // Progreso Historia
    Route::get('/progreso-historia', [ProgresoHistoriaController::class, 'index']);
    Route::post('/progreso-historia', [ProgresoHistoriaController::class, 'store']);
    Route::get('/users/progreso-historia', [ProgresoHistoriaController::class, 'getProgresoModoHistoriaUsuario']);
    Route::get('/users/porcentaje-historia', [ProgresoHistoriaController::class, 'getPorcentajeUsuarioModoHistoria']);

    // Roguelike
    Route::apiResource('runs-roguelike', RunsRoguelikeController::class);
    Route::get('/users/mejor-run', [RunsRoguelikeController::class, 'getNivelMejorRunUsuario']);


    // Niveles Roguelike (protegido: devuelve test_cases)
    Route::get('/niveles-roguelike/aleatorio', [NivelesRoguelikeController::class, 'getNivelModoInfinito']);

    // Ejecutar código
    Route::post('/ejecutar-codigo', [EjecutarCodigo::class, 'ejecutarCodigo']);

    // Roguelike Session Management (Anti-Cheat)
    Route::post('/roguelike/start-session', [RoguelikeSessionController::class, 'startSession']);
    Route::post('/roguelike/start-level', [RoguelikeSessionController::class, 'startLevel']);
    Route::get('/roguelike/check-time', [RoguelikeSessionController::class, 'checkTime']);
    Route::post('/roguelike/failure', [RoguelikeSessionController::class, 'registerFailure']);
    Route::post('/roguelike/success', [RoguelikeSessionController::class, 'registerSuccess']);
    Route::get('/roguelike/session', [RoguelikeSessionController::class, 'getSessionStatus']);
    Route::post('/roguelike/buy-mejora', [RoguelikeSessionController::class, 'buyMejora']);

    // Temas
    Route::get('/temas', [TemaController::class, 'index']);
    Route::get('/temas/mis-temas', [TemaController::class, 'misTemas']);
    Route::post('/temas/{tema}/comprar', [TemaController::class, 'comprar']);
    Route::post('/temas/{tema}/activar', [TemaController::class, 'activar']);

    // Perfil
    Route::get('/users/data', [UserController::class, 'getUserData']);
    Route::get('/users/mi-posicion', [UserController::class, 'getMiPosicionRanking']);
    Route::post('/logout', [UserController::class, 'logout']);
    Route::post('/users/preferencias', [UserController::class, 'updatePreferencias']);
    Route::post('/users/desactivar', [UserController::class, 'desactivarPropiaCuenta']);

    // Battle Pass / Premium (Stripe)
    Route::get('/battle-pass/status', [PurchaseController::class, 'getBattlePassStatus']);
    Route::post('/battle-pass/create-intent', [PurchaseController::class, 'createPaymentIntent']);
    Route::post('/battle-pass/confirm', [PurchaseController::class, 'confirmPayment']);

    // Reportes (Usuario común manda reportes)
    Route::post('/reportes', [\App\Http\Controllers\ReporteController::class, 'store'])->middleware('throttle:3,10');

    // Administración de contenido y usuarios (Solo Admin)
    Route::middleware('admin')->group(function () {
        // Rutas de Administración para Niveles Roguelike
        Route::get('/admin/niveles-roguelike', [NivelesRoguelikeController::class, 'indexAdmin']);
        Route::get('/admin/niveles-roguelike/desactivados', [NivelesRoguelikeController::class, 'desactivados']);
        Route::post('/admin/niveles-roguelike', [NivelesRoguelikeController::class, 'store']);
        Route::put('/admin/niveles-roguelike/{id}', [NivelesRoguelikeController::class, 'update']);
        Route::delete('/admin/niveles-roguelike/{id}', [NivelesRoguelikeController::class, 'destroy']);
        Route::get('/admin/niveles-roguelike/{id}', [NivelesRoguelikeController::class, 'show']);
        Route::post('/admin/niveles-roguelike/{id}/toggle-status', [NivelesRoguelikeController::class, 'toggleStatus']);

        Route::apiResource('mejoras', MejorasController::class)->except(['index', 'show']);

        // Gestión de Usuarios Completa (CRUD Admin)
        Route::get('/admin/users', [UserController::class, 'index']);
        Route::delete('/admin/users/{id}', [UserController::class, 'destroy']);
        Route::post('/admin/users/{id}/toggle-status', [UserController::class, 'toggleStatus']);

        // Gestión de Reportes (Revisión por Moderadores)
        Route::get('/admin/reportes', [\App\Http\Controllers\ReporteController::class, 'index']);
        Route::get('/admin/reportes/{id}', [\App\Http\Controllers\ReporteController::class, 'show']);
        Route::put('/admin/reportes/{id}', [\App\Http\Controllers\ReporteController::class, 'update']); // Para cambiar estado/prioridad
        Route::delete('/admin/reportes/{id}', [\App\Http\Controllers\ReporteController::class, 'destroy']);

        // Gestión de Niveles Historia
        Route::get('/admin/niveles-historia', [NivelesHistoriaController::class, 'indexAdmin']);
        Route::get('/admin/niveles-historia/desactivados', [NivelesHistoriaController::class, 'desactivados']);
        Route::post('/admin/niveles-historia', [NivelesHistoriaController::class, 'store']);
        Route::put('/admin/niveles-historia/{id}', [NivelesHistoriaController::class, 'update']);
        Route::delete('/admin/niveles-historia/{id}', [NivelesHistoriaController::class, 'destroy']);
        Route::get('/admin/niveles-historia/{id}', [NivelesHistoriaController::class, 'show']);
        Route::post('/admin/niveles-historia/{id}/toggle-status', [NivelesHistoriaController::class, 'toggleStatus']);

        // Dashboard Stats y Logs
        Route::get('/admin/stats', [AdminDashboardController::class, 'getStats']);
        Route::get('/admin/logs', [AdminDashboardController::class, 'getLogs']);

        // Herramientas de Depuración Roguelike (Solo SuperAdmin)
        Route::post('/roguelike/debug-set-time', [RoguelikeSessionController::class, 'debugSetTime']);
    });
});
