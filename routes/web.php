<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\ResolucionController;
use App\Http\Controllers\TipoSolicitudController;
use App\Http\Controllers\EstadoSolicitudController;
use App\Http\Controllers\ParentescoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\TipoVarioController;

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
    */
    Route::middleware(['rol:funcionario'])->group(function () {

        Route::prefix('solicitudes')->name('solicitudes.')->group(function () {
            Route::get('/', [SolicitudController::class, 'index'])->name('index');       // Listado personal
            Route::get('/crear/{tipo}', [SolicitudController::class, 'create'])->name('create'); // Formulario según tipo
            Route::post('/', [SolicitudController::class, 'store'])->name('store');      // Enviar solicitud
        });
    });


    /*
    |--------------------------------------------------------------------------
    | DETALLE DE SOLICITUD (accesible por jefatura, secretaria, admin o el propio usuario)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth'])->get('/solicitudes/{id}', [SolicitudController::class, 'show'])
        ->name('solicitudes.show');

    // Ficha PDF imprimible de la solicitud
    Route::get('/solicitudes/{solicitud}/pdf', [SolicitudController::class, 'pdf'])
        ->name('solicitudes.pdf')
        ->middleware('auth');

    /*
    |--------------------------------------------------------------------------
    | JEFATURA (Secretaria / Inspector General / Director) Reportes mensuales
    |--------------------------------------------------------------------------
    */
    Route::prefix('reportes')->middleware(['auth'])->name('reportes.')->group(function () {
        Route::get('/mensual', [ReporteController::class, 'reporteMensual'])
            ->name('mensual');
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
            return view('dashboard.secretaria'); 
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
        Route::get('/tipos-solicitud/{id}/edit', [TipoSolicitudController::class, 'edit'])->name('tipos.edit');
        Route::put('/tipos-solicitud/{id}', [TipoSolicitudController::class, 'update'])->name('tipos.update');
        Route::delete('/tipos-solicitud/{id}', [TipoSolicitudController::class, 'destroy'])->name('tipos.destroy');

        // Estados de solicitud
        Route::get('/estados-solicitud', [EstadoSolicitudController::class, 'index'])->name('estados.index');
        Route::post('/estados-solicitud', [EstadoSolicitudController::class, 'store'])->name('estados.store');
        Route::get('/estados-solicitud/{id}/edit', [EstadoSolicitudController::class, 'edit'])->name('estados.edit');
        Route::put('/estados-solicitud/{id}', [EstadoSolicitudController::class, 'update'])->name('estados.update');
        Route::delete('/estados-solicitud/{id}', [EstadoSolicitudController::class, 'destroy'])->name('estados.destroy');

        // Parentescos
        Route::get('/parentescos', [ParentescoController::class, 'index'])->name('parentescos.index');
        Route::post('/parentescos', [ParentescoController::class, 'store'])->name('parentescos.store');
        Route::get('/parentescos/{id}/edit', [ParentescoController::class, 'edit'])->name('parentescos.edit');
        Route::put('/parentescos/{id}', [ParentescoController::class, 'update'])->name('parentescos.update');
        Route::delete('/parentescos/{id}', [ParentescoController::class, 'destroy'])->name('parentescos.destroy');

        // Tipos Varios
        Route::get('/tipos-varios', [TipoVarioController::class, 'index'])->name('tiposvarios.index');
        Route::post('/tipos-varios', [TipoVarioController::class, 'store'])->name('tiposvarios.store');
        Route::get('/tipos-varios/{id}/edit', [TipoVarioController::class, 'edit'])->name('tiposvarios.edit');
        Route::put('/tipos-varios/{id}', [TipoVarioController::class, 'update'])->name('tiposvarios.update');
        Route::delete('/tipos-varios/{id}', [TipoVarioController::class, 'destroy'])->name('tiposvarios.destroy');
    });
});
