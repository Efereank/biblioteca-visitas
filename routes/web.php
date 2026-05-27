<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VisitanteController;
use App\Http\Controllers\VisitaController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TipoVisitanteController;
use App\Http\Controllers\PropositoVisitaController;
use App\Http\Controllers\ActividadController;
use App\Http\Controllers\SalaController;
use App\Http\Controllers\PerfilInteresController;
use App\Http\Controllers\SubcategoriaInteresController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UbicacionController;

// Redirección según rol
Route::get('/', function () {
    $user = Auth::user();
    if ($user) {
        if (isset($user->role) && $user->role === 'admin') {
            return redirect()->route('dashboard');
        }
        return redirect()->route('visitas.create');
    }
    return redirect()->route('login');
});

// APIs públicas (sin auth)
Route::get('api/visitantes/cedula/{cedula}', [VisitanteController::class, 'searchByCedula'])
    ->name('api.visitantes.cedula');

Route::get('api/visitantes/{visitanteId}/visita-activa', [VisitaController::class, 'verificarVisitaActiva'])
    ->name('api.visitantes.visita-activa');

// QR de visitante (accesible por todos los roles)
Route::get('visitantes/{visitante}/qr', [VisitanteController::class, 'generarQR'])
    ->middleware(['auth'])
    ->name('visitantes.qr');

Route::get('/visitantes/{id}/menores-activos', [VisitanteController::class, 'menoresActivos']);
Route::get('/visitantes/{id}/visita-activa', [VisitaController::class, 'verificarVisitaActiva']);


// Dashboard y reportes solo admin
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('reportes', [ReporteController::class, 'index'])->name('reportes');
    Route::get('reportes/exportar-excel', [ReporteController::class, 'exportarExcel'])->name('reportes.excel');
    Route::get('reportes/exportar-pdf', [ReporteController::class, 'exportarPDF'])->name('reportes.pdf');
});

// Registro de visita e historial accesible a todos
Route::middleware(['auth'])->group(function () {
    Route::get('registro-visita', [VisitaController::class, 'create'])->name('visitas.create');
    Route::post('visitas', [VisitaController::class, 'store'])->name('visitas.store');
    Route::get('historial', [VisitaController::class, 'historial'])->name('visitas.historial');
    Route::post('visitas/{visita}/salida', [VisitaController::class, 'registrarSalida'])->name('visitas.salida');
});

// Visitantes (admin y recepcionista)
Route::middleware(['auth', 'role:admin,recepcionista'])->group(function () {
    Route::resource('visitantes', VisitanteController::class)->except(['create', 'edit']);
});

// Perfil de usuario (todos)
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Administración solo admin
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('users', UserController::class)->except(['show']);
    Route::resource('tipos-visitante', TipoVisitanteController::class)->except(['show', 'create', 'edit']);
    Route::resource('propositos-visita', PropositoVisitaController::class)->except(['show', 'create', 'edit']);
    Route::resource('actividades', ActividadController::class)->except(['show', 'create', 'edit']);
    Route::resource('salas', SalaController::class)->except(['show', 'create', 'edit']);
    Route::resource('perfiles-interes', PerfilInteresController::class)->except(['show', 'create', 'edit']);
    Route::resource('subcategorias-interes', SubcategoriaInteresController::class)->except(['show', 'create', 'edit']);

    Route::resource('municipios', \App\Http\Controllers\MunicipioController::class)->except(['show']);
    Route::resource('parroquias', \App\Http\Controllers\ParroquiaController::class)->except(['show']);
    Route::resource('ciudades', \App\Http\Controllers\CiudadController::class)->except(['show']);
});


Route::get('api/parroquias/{municipio}', [UbicacionController::class, 'getParroquias']);
Route::get('api/ciudades/{municipio}', [UbicacionController::class, 'getCiudades']);
Route::get('api/municipios', function() {
    return \App\Models\Municipio::all();
});

// Imagen PNG para mostrar en <img>
Route::get('visitantes/{visitante}/qr', [VisitanteController::class, 'generarQR'])->name('visitantes.qr');
// Vista con ticket imprimible
Route::get('visitantes/{visitante}/ticket', [VisitanteController::class, 'verQR'])->name('visitantes.ticket');
require __DIR__.'/auth.php';
