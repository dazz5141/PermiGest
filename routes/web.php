<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\ResolucionController;
use App\Http\Controllers\TipoSolicitudController;
use App\Http\Controllers\EstadoSolicitudController;
use App\Http\Controllers\ParentescoController;

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS (sin autenticación)
|--------------------------------------------------------------------------
| Solo acceso a login / logout.
|--------------------------------------------------------------------------
*/

// Página de login
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');

// Procesar login
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// Cerrar sesión
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


/*
|--------------------------------------------------------------------------
| RUTAS PROTEGIDAS (requieren autenticación)
|--------------------------------------------------------------------------
| Acceso solo a usuarios logeados. 
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD GENERAL (redirección por rol)
    |--------------------------------------------------------------------------
    */
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | FUNCIONARIO (docente o asistente)
    |--------------------------------------------------------------------------
    | Puede crear, ver y revisar el historial de sus solicitudes.
    |--------------------------------------------------------------------------
    */
    Route::middleware(['rol:funcionario'])->group(function () {

        Route::prefix('solicitudes')->name('solicitudes.')->group(function () {
            Route::get('/', [SolicitudController::class, 'index'])->name('index');       // Listado personal
            Route::get('/crear/{tipo}', [SolicitudController::class, 'create'])->name('create'); // Formulario según tipo
            Route::post('/', [SolicitudController::class, 'store'])->name('store');      // Enviar solicitud
            Route::get('/{id}', [SolicitudController::class, 'show'])->name('show');     // Ver detalle
        });
    });


    /*
    |--------------------------------------------------------------------------
    | JEFATURA (Inspector General / Director)
    |--------------------------------------------------------------------------
    | Puede revisar y aprobar/rechazar solicitudes de su equipo.
    |--------------------------------------------------------------------------
    */
    Route::middleware(['rol:jefe_directo'])->prefix('resoluciones')->name('resoluciones.')->group(function () {
        Route::get('/', [ResolucionController::class, 'index'])->name('index');      // Listado de solicitudes a revisar
        Route::post('/{id}', [ResolucionController::class, 'update'])->name('update'); // Aprobar/Rechazar
    });


    /*
    |--------------------------------------------------------------------------
    | SECRETARÍA / ADMINISTRATIVO
    |--------------------------------------------------------------------------
    | Puede generar reportes y revisar el estado general del establecimiento.
    |--------------------------------------------------------------------------
    */
    Route::middleware(['rol:secretaria'])->group(function () {
        Route::get('/reportes/mensuales', function () {
            return view('dashboard.secretaria'); // Ejemplo inicial
        })->name('reportes.mensuales');
    });


    /*
    |--------------------------------------------------------------------------
    | ADMINISTRADOR
    |--------------------------------------------------------------------------
    | Gestiona catálogos base: tipos, estados, parentescos.
    |--------------------------------------------------------------------------
    */
    Route::middleware(['rol:admin'])->prefix('admin')->group(function () {

        // Tipos de solicitud
        Route::get('/tipos-solicitud', [TipoSolicitudController::class, 'index'])->name('tipos.index');
        Route::post('/tipos-solicitud', [TipoSolicitudController::class, 'store'])->name('tipos.store');
        Route::delete('/tipos-solicitud/{id}', [TipoSolicitudController::class, 'destroy'])->name('tipos.destroy');

        // Estados de solicitud
        Route::get('/estados-solicitud', [EstadoSolicitudController::class, 'index'])->name('estados.index');
        Route::post('/estados-solicitud', [EstadoSolicitudController::class, 'store'])->name('estados.store');
        Route::delete('/estados-solicitud/{id}', [EstadoSolicitudController::class, 'destroy'])->name('estados.destroy');

        // Parentescos
        Route::get('/parentescos', [ParentescoController::class, 'index'])->name('parentescos.index');
        Route::post('/parentescos', [ParentescoController::class, 'store'])->name('parentescos.store');
        Route::delete('/parentescos/{id}', [ParentescoController::class, 'destroy'])->name('parentescos.destroy');
    });
});
