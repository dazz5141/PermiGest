<?php

use App\Http\Controllers\Admin\FeriadoController;
use App\Http\Controllers\Admin\RestauracionPermisoController;
use App\Http\Controllers\Admin\RolController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EstadoSolicitudController;
use App\Http\Controllers\ParentescoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ResolucionController;
use App\Http\Controllers\ResumenPermisosController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\TipoSolicitudController;
use App\Http\Controllers\TipoVarioController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware(['rol:funcionario'])->group(function () {
        Route::prefix('solicitudes')->name('solicitudes.')->group(function () {
            Route::get('/', [SolicitudController::class, 'index'])->name('index');
            Route::get('/crear/{tipo}', [SolicitudController::class, 'create'])->name('create');
            Route::post('/', [SolicitudController::class, 'store'])->name('store');
        });

        Route::get('/mis-permisos/resumen', [ResumenPermisosController::class, 'index'])->name('mis-permisos.resumen');
    });

    Route::get('/solicitudes/{id}', [SolicitudController::class, 'show'])->name('solicitudes.show');
    Route::get('/solicitudes/{solicitud}/pdf', [SolicitudController::class, 'pdf'])->name('solicitudes.pdf');

    Route::prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/mensual', [ReporteController::class, 'reporteMensual'])->name('mensual');
    });

    Route::middleware(['rol:jefe_directo'])->prefix('resoluciones')->name('resoluciones.')->group(function () {
        Route::get('/', [ResolucionController::class, 'index'])->name('index');
        Route::post('/{id}', [ResolucionController::class, 'update'])->name('update');
    });

    Route::middleware(['rol:secretaria'])->group(function () {
        Route::get('/reportes/mensuales', function () {
            return view('dashboard.secretaria');
        })->name('reportes.mensuales');
    });

    Route::middleware(['rol:admin,encargado_sistema'])->prefix('admin')->group(function () {
        Route::get('/usuarios', [UsuarioController::class, 'index'])->name('admin.usuarios.index');
        Route::post('/usuarios', [UsuarioController::class, 'store'])->name('admin.usuarios.store');
        Route::get('/usuarios/{id}/edit', [UsuarioController::class, 'edit'])->name('admin.usuarios.edit');
        Route::put('/usuarios/{id}', [UsuarioController::class, 'update'])->name('admin.usuarios.update');
        Route::post('/usuarios/{id}/toggle', [UsuarioController::class, 'toggle'])->name('admin.usuarios.toggle');
        Route::post('/usuarios/{id}/reset-password', [UsuarioController::class, 'resetPassword'])->name('admin.usuarios.reset');

        Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
        Route::resource('feriados', FeriadoController::class)->names('admin.feriados')->except('show');
        Route::get('/restauraciones', [RestauracionPermisoController::class, 'index'])->name('admin.restauraciones.index');
        Route::post('/restauraciones', [RestauracionPermisoController::class, 'store'])->name('admin.restauraciones.store');
    });

    Route::middleware(['rol:admin'])->prefix('admin')->group(function () {
        Route::get('/tipos-solicitud', [TipoSolicitudController::class, 'index'])->name('tipos.index');
        Route::post('/tipos-solicitud', [TipoSolicitudController::class, 'store'])->name('tipos.store');
        Route::get('/tipos-solicitud/{id}/edit', [TipoSolicitudController::class, 'edit'])->name('tipos.edit');
        Route::put('/tipos-solicitud/{id}', [TipoSolicitudController::class, 'update'])->name('tipos.update');
        Route::delete('/tipos-solicitud/{id}', [TipoSolicitudController::class, 'destroy'])->name('tipos.destroy');

        Route::get('/estados-solicitud', [EstadoSolicitudController::class, 'index'])->name('estados.index');
        Route::post('/estados-solicitud', [EstadoSolicitudController::class, 'store'])->name('estados.store');
        Route::get('/estados-solicitud/{id}/edit', [EstadoSolicitudController::class, 'edit'])->name('estados.edit');
        Route::put('/estados-solicitud/{id}', [EstadoSolicitudController::class, 'update'])->name('estados.update');
        Route::delete('/estados-solicitud/{id}', [EstadoSolicitudController::class, 'destroy'])->name('estados.destroy');

        Route::get('/parentescos', [ParentescoController::class, 'index'])->name('parentescos.index');
        Route::post('/parentescos', [ParentescoController::class, 'store'])->name('parentescos.store');
        Route::get('/parentescos/{id}/edit', [ParentescoController::class, 'edit'])->name('parentescos.edit');
        Route::put('/parentescos/{id}', [ParentescoController::class, 'update'])->name('parentescos.update');
        Route::delete('/parentescos/{id}', [ParentescoController::class, 'destroy'])->name('parentescos.destroy');

        Route::get('/tipos-varios', [TipoVarioController::class, 'index'])->name('tiposvarios.index');
        Route::post('/tipos-varios', [TipoVarioController::class, 'store'])->name('tiposvarios.store');
        Route::get('/tipos-varios/{id}/edit', [TipoVarioController::class, 'edit'])->name('tiposvarios.edit');
        Route::put('/tipos-varios/{id}', [TipoVarioController::class, 'update'])->name('tiposvarios.update');
        Route::delete('/tipos-varios/{id}', [TipoVarioController::class, 'destroy'])->name('tiposvarios.destroy');

        Route::get('/roles', [RolController::class, 'index'])->name('admin.roles.index');
        Route::post('/roles', [RolController::class, 'store'])->name('admin.roles.store');
        Route::get('/roles/{id}/edit', [RolController::class, 'edit'])->name('admin.roles.edit');
        Route::put('/roles/{id}', [RolController::class, 'update'])->name('admin.roles.update');
        Route::delete('/roles/{id}', [RolController::class, 'destroy'])->name('admin.roles.destroy');
    });
});
