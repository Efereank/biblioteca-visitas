<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VisitanteController;
use App\Http\Controllers\VisitaController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::get('api/visitantes/cedula/{cedula}', [VisitanteController::class, 'searchByCedula'])
    ->name('api.visitantes.cedula');

Route::get('api/visitantes/{visitanteId}/visita-activa', [VisitaController::class, 'verificarVisitaActiva'])
    ->name('api.visitantes.visita-activa');

Route::middleware(['auth'])->group(function () {
    Route::resource('visitantes', VisitanteController::class)->except(['create', 'edit']);
    Route::get('registro-visita', [VisitaController::class, 'create'])->name('visitas.create');
    Route::post('visitas', [VisitaController::class, 'store'])->name('visitas.store');
    Route::post('visitas/{visita}/salida', [VisitaController::class, 'registrarSalida'])->name('visitas.salida');
    Route::get('historial', [VisitaController::class, 'historial'])->name('visitas.historial');
    Route::get('reportes', [ReporteController::class, 'index'])->name('reportes');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
