<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Visita;
use App\Models\Sala;
use App\Models\TipoVisitante;
use App\Models\PropositoVisita;
use Carbon\Carbon;
use App\Exports\ReportesExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', Carbon::now('America/Caracas')->subMonth()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', Carbon::now('America/Caracas')->endOfMonth()->format('Y-m-d'));

        $fechaInicioObj = Carbon::parse($fechaInicio);
        $fechaFinObj = Carbon::parse($fechaFin)->endOfDay();

        $salas = Sala::withCount(['visitas' => function($q) use ($fechaInicioObj, $fechaFinObj) {
            $q->whereBetween('fecha_hora_entrada', [$fechaInicioObj, $fechaFinObj]);
        }])->get();

        $salasLabels = $salas->pluck('nombre')->toArray();
        $salasData = $salas->pluck('visitas_count')->toArray();

        $flujoHorario = array_fill(0, 24, 0);
        $horasLabels = [];
        for ($i = 0; $i < 24; $i++) {
            $horasLabels[] = sprintf('%02d:00', $i);
        }

        $visitasPorHora = Visita::whereBetween('fecha_hora_entrada', [$fechaInicioObj, $fechaFinObj])
            ->get()
            ->groupBy(function($visita) {
                return $visita->fecha_hora_entrada->format('H');
            });

        foreach ($visitasPorHora as $hora => $visitas) {
            $flujoHorario[intval($hora)] = $visitas->count();
        }

        $diasLabels = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $diasData = array_fill(0, 7, 0);

        $visitasPorDia = Visita::whereBetween('fecha_hora_entrada', [$fechaInicioObj, $fechaFinObj])
            ->get()
            ->groupBy(function($visita) {
                return $visita->fecha_hora_entrada->dayOfWeek;
            });

        foreach ($visitasPorDia as $dia => $visitas) {
            $diasData[$dia] = $visitas->count();
        }

        return view('reportes.index', compact(
            'salasLabels',
            'salasData',
            'horasLabels',
            'flujoHorario',
            'diasLabels',
            'diasData',
            'fechaInicio',
            'fechaFin'
        ));
    }

    public function exportarExcel(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));

        return Excel::download(new ReportesExport($fechaInicio, $fechaFin), 'reporte_biblioteca_' . date('Y-m-d') . '.xlsx');
    }

public function exportarPDF(Request $request)
{
    $fechaInicio = $request->input('fecha_inicio', Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d'));
    $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));

    $fechaInicioObj = Carbon::parse($fechaInicio)->startOfDay();
    $fechaFinObj = Carbon::parse($fechaFin)->endOfDay();

    $totalVisitas = Visita::whereBetween('fecha_hora_entrada', [$fechaInicioObj, $fechaFinObj])->count();

    $dias = $fechaInicioObj->diffInDays($fechaFinObj) + 1;
    $promedioDiario = $dias > 0 ? round($totalVisitas / $dias, 1) : 0;

    $salas = Sala::withCount(['visitas' => function ($q) use ($fechaInicioObj, $fechaFinObj) {
        $q->whereBetween('fecha_hora_entrada', [$fechaInicioObj, $fechaFinObj]);
    }])->orderBy('nombre')->get();

    $tipos = TipoVisitante::leftJoin('visitantes', 'tipos_visitante.id', '=', 'visitantes.tipo_visitante_id')
        ->leftJoin('visitas', function ($join) use ($fechaInicioObj, $fechaFinObj) {
            $join->on('visitantes.id', '=', 'visitas.visitante_id')
                 ->whereBetween('visitas.fecha_hora_entrada', [$fechaInicioObj, $fechaFinObj]);
        })
        ->select('tipos_visitante.id', 'tipos_visitante.nombre', DB::raw('COUNT(visitas.id) as visitas_count'))
        ->groupBy('tipos_visitante.id', 'tipos_visitante.nombre')
        ->orderBy('tipos_visitante.nombre')
        ->get();

    $propositos = PropositoVisita::withCount(['visitas' => function ($q) use ($fechaInicioObj, $fechaFinObj) {
        $q->whereBetween('fecha_hora_entrada', [$fechaInicioObj, $fechaFinObj]);
    }])->orderByDesc('visitas_count')->get();


    $diaPico = Visita::whereBetween('fecha_hora_entrada', [$fechaInicioObj, $fechaFinObj])
        ->select(DB::raw('DATE(fecha_hora_entrada) as fecha'), DB::raw('COUNT(*) as total'))
        ->groupBy('fecha')
        ->orderByDesc('total')
        ->first();

    $horaPico = Visita::whereBetween('fecha_hora_entrada', [$fechaInicioObj, $fechaFinObj])
        ->select(DB::raw('HOUR(fecha_hora_entrada) as hora'), DB::raw('COUNT(*) as total'))
        ->groupBy('hora')
        ->orderByDesc('total')
        ->first();
    $duracionPromedio = Visita::whereBetween('fecha_hora_entrada', [$fechaInicioObj, $fechaFinObj])
        ->whereNotNull('fecha_hora_salida')
        ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, fecha_hora_entrada, fecha_hora_salida)) as promedio'))
        ->first()->promedio ?? 0;

    $minutosTotales = Visita::whereBetween('fecha_hora_entrada', [$fechaInicioObj, $fechaFinObj])
        ->whereNotNull('fecha_hora_salida')
        ->select(DB::raw('SUM(TIMESTAMPDIFF(MINUTE, fecha_hora_entrada, fecha_hora_salida)) as total'))
        ->first()->total ?? 0;
    $horasTotales = round($minutosTotales / 60, 1);

    $nuevosVisitas = Visita::whereBetween('visitas.fecha_hora_entrada', [$fechaInicioObj, $fechaFinObj])
        ->join('visitantes', 'visitas.visitante_id', '=', 'visitantes.id')
        ->where('visitantes.fecha_registro', '>=', $fechaInicioObj)
        ->where('visitantes.fecha_registro', '<=', $fechaFinObj)
        ->count();
    $recurrentesVisitas = $totalVisitas - $nuevosVisitas;
    $pctNuevos = $totalVisitas > 0 ? round(($nuevosVisitas / $totalVisitas) * 100) : 0;
    $pctRecurrentes = 100 - $pctNuevos;

    $visitasPorGenero = Visita::whereBetween('visitas.fecha_hora_entrada', [$fechaInicioObj, $fechaFinObj])
        ->join('visitantes', 'visitas.visitante_id', '=', 'visitantes.id')
        ->select('visitantes.genero', DB::raw('COUNT(*) as total'))
        ->groupBy('visitantes.genero')
        ->get()
        ->map(function ($item) {
            $item->genero = $item->genero ?: 'No especificado';
            return $item;
        });

    $visitasPorEdad = Visita::whereBetween('visitas.fecha_hora_entrada', [$fechaInicioObj, $fechaFinObj])
        ->join('visitantes', 'visitas.visitante_id', '=', 'visitantes.id')
        ->select(DB::raw("
            CASE
                WHEN TIMESTAMPDIFF(YEAR, visitantes.fecha_nacimiento, CURDATE()) < 18 THEN 'Menor de edad'
                WHEN TIMESTAMPDIFF(YEAR, visitantes.fecha_nacimiento, CURDATE()) BETWEEN 18 AND 30 THEN '18 - 30 años'
                WHEN TIMESTAMPDIFF(YEAR, visitantes.fecha_nacimiento, CURDATE()) BETWEEN 31 AND 50 THEN '31 - 50 años'
                WHEN TIMESTAMPDIFF(YEAR, visitantes.fecha_nacimiento, CURDATE()) > 50 THEN 'Mayor de 50 años'
                ELSE 'Sin datos'
            END as grupo_etario
        "), DB::raw('COUNT(*) as total'))
        ->groupBy('grupo_etario')
        ->get();

    $topDias = Visita::whereBetween('fecha_hora_entrada', [$fechaInicioObj, $fechaFinObj])
        ->select(DB::raw('DATE(fecha_hora_entrada) as fecha'), DB::raw('COUNT(*) as total'))
        ->groupBy('fecha')
        ->orderByDesc('total')
        ->limit(5)
        ->get();

    $pdf = Pdf::loadView('reportes.pdf', compact(
        'fechaInicio', 'fechaFin',
        'totalVisitas', 'promedioDiario',
        'salas', 'tipos', 'propositos',
        'diaPico', 'horaPico', 'duracionPromedio', 'horasTotales',
        'nuevosVisitas', 'recurrentesVisitas', 'pctNuevos', 'pctRecurrentes',
        'visitasPorGenero', 'visitasPorEdad',
        'topDias'
    ));

    return $pdf->download('reporte_biblioteca_' . date('Y-m-d') . '.pdf');
}
}
