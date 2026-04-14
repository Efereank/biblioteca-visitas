<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VisitanteController;
use App\Http\Controllers\VisitaController;
use App\Http\Controllers\ReporteController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Rutas API para búsqueda de visitantes por cédula y verificación de visita activa
Route::get('api/visitantes/cedula/{cedula}', [VisitanteController::class, 'searchByCedula'])
    ->name('api.visitantes.cedula');

Route::get('api/visitantes/{visitanteId}/visita-activa', [VisitaController::class, 'verificarVisitaActiva'])
    ->name('api.visitantes.visita-activa');

// Visitantes resource (menos create y edit)
Route::resource('visitantes', VisitanteController::class)->except(['create', 'edit']);

// Visitas
Route::get('registro-visita', [VisitaController::class, 'create'])->name('visitas.create');
Route::post('visitas', [VisitaController::class, 'store'])->name('visitas.store');
Route::post('visitas/{visita}/salida', [VisitaController::class, 'registrarSalida'])->name('visitas.salida');
Route::get('historial', [VisitaController::class, 'historial'])->name('visitas.historial');

// Reportes
Route::get('reportes', [ReporteController::class, 'index'])->name('reportes');
