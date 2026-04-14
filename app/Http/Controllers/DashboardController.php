<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Visita;
use App\Models\Visitante;
use App\Models\TipoVisitante;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Consultas directas
        $visitasHoy = Visita::whereDate('fecha_hora_entrada', Carbon::today())->count();

        $visitantesActivos = Visita::whereDate('fecha_hora_entrada', Carbon::today())
                                    ->whereNull('fecha_hora_salida')
                                    ->count();

        // Promedio diario de visitas
        $promedioDiario = Visita::selectRaw('COUNT(*) as total')
                                ->groupByRaw('DATE(fecha_hora_entrada)')
                                ->get()
                                ->avg('total') ?? 0;

        // Visitantes frecuentes (5+ visitas)
        $frecuentes = Visitante::where('visitas_count', '>=', 5)->count();

        // Datos para gráfico de barras horizontales
        $tipos = TipoVisitante::select('tipos_visitante.id', 'tipos_visitante.nombre', 'tipos_visitante.color')
            ->selectRaw('COUNT(DISTINCT visitas.id) as total_visitas')
            ->leftJoin('visitantes', 'tipos_visitante.id', '=', 'visitantes.tipo_visitante_id')
            ->leftJoin('visitas', 'visitantes.id', '=', 'visitas.visitante_id')
            ->groupBy('tipos_visitante.id', 'tipos_visitante.nombre', 'tipos_visitante.color')
            ->get();

        $tiposLabels = $tipos->pluck('nombre');
        $tiposData = $tipos->pluck('total_visitas');
        $tiposColors = $tipos->pluck('color');

        // Últimas visitas para la tabla
        $ultimasVisitas = Visita::with(['visitante', 'proposito'])
                                ->orderBy('fecha_hora_entrada', 'desc')
                                ->limit(10)
                                ->get();

        return view('dashboard', compact(
            'visitasHoy',
            'visitantesActivos',
            'promedioDiario',
            'frecuentes',
            'tiposLabels',
            'tiposData',
            'tiposColors',
            'ultimasVisitas'
        ));
    }
}
