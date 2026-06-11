<?php

namespace App\Services;

use App\Models\Visita;
use App\Models\Sala;
use App\Models\TipoVisitante;
use App\Models\PropositoVisita;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReporteService
{
    /**
     * Obtener datos del panel de reportes (gráficos).
     */
    public function obtenerDatosPanel($fechaInicio, $fechaFin)
    {
        $fechaInicioObj = Carbon::parse($fechaInicio);
        $fechaFinObj = Carbon::parse($fechaFin)->endOfDay();

        // Salas más visitadas
        $salas = Sala::withCount(['visitas' => function($q) use ($fechaInicioObj, $fechaFinObj) {
            $q->whereBetween('fecha_hora_entrada', [$fechaInicioObj, $fechaFinObj]);
        }])->get();

        // Flujo horario
        $flujoHorario = array_fill(0, 24, 0);
        $visitasPorHora = Visita::whereBetween('fecha_hora_entrada', [$fechaInicioObj, $fechaFinObj])
            ->get()
            ->groupBy(function($visita) {
                return $visita->fecha_hora_entrada->format('H');
            });

        foreach ($visitasPorHora as $hora => $visitas) {
            $flujoHorario[intval($hora)] = $visitas->count();
        }

        // Días de la semana
        $diasData = array_fill(0, 7, 0);
        $visitasPorDia = Visita::whereBetween('fecha_hora_entrada', [$fechaInicioObj, $fechaFinObj])
            ->get()
            ->groupBy(function($visita) {
                return $visita->fecha_hora_entrada->dayOfWeek;
            });

        foreach ($visitasPorDia as $dia => $visitas) {
            $diasData[$dia] = $visitas->count();
        }

        return [
            'salasLabels' => $salas->pluck('nombre')->toArray(),
            'salasData' => $salas->pluck('visitas_count')->toArray(),
            'flujoHorario' => $flujoHorario,
            'diasData' => $diasData,
        ];
    }

    /**
     * Obtener todos los datos para el reporte PDF.
     */
    public function obtenerDatosReporte($fechaInicio, $fechaFin)
    {
        $fechaInicioObj = Carbon::parse($fechaInicio)->startOfDay();
        $fechaFinObj = Carbon::parse($fechaFin)->endOfDay();

        $totalVisitas = Visita::whereBetween('fecha_hora_entrada', [$fechaInicioObj, $fechaFinObj])->count();
        $dias = $fechaInicioObj->diffInDays($fechaFinObj) + 1;
        $promedioDiario = $dias > 0 ? round($totalVisitas / $dias, 1) : 0;

        return [
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'totalVisitas' => $totalVisitas,
            'promedioDiario' => $promedioDiario,
        ];
    }

    /**
     * Obtener distribución por salas.
     */
    public function distribucionPorSalas($fechaInicioObj, $fechaFinObj)
    {
        return Sala::withCount(['visitas' => function ($q) use ($fechaInicioObj, $fechaFinObj) {
            $q->whereBetween('fecha_hora_entrada', [$fechaInicioObj, $fechaFinObj]);
        }])->orderBy('nombre')->get();
    }

    /**
     * Obtener distribución por tipos de visitante.
     */
    public function distribucionPorTipos($fechaInicioObj, $fechaFinObj)
    {
        return TipoVisitante::leftJoin('visitantes', 'tipos_visitante.id', '=', 'visitantes.tipo_visitante_id')
            ->leftJoin('visitas', function ($join) use ($fechaInicioObj, $fechaFinObj) {
                $join->on('visitantes.id', '=', 'visitas.visitante_id')
                     ->whereBetween('visitas.fecha_hora_entrada', [$fechaInicioObj, $fechaFinObj]);
            })
            ->select('tipos_visitante.id', 'tipos_visitante.nombre', DB::raw('COUNT(visitas.id) as visitas_count'))
            ->groupBy('tipos_visitante.id', 'tipos_visitante.nombre')
            ->orderBy('tipos_visitante.nombre')
            ->get();
    }

    /**
     * Obtener distribución por propósitos.
     */
    public function distribucionPorPropositos($fechaInicioObj, $fechaFinObj)
    {
        return PropositoVisita::withCount(['visitas' => function ($q) use ($fechaInicioObj, $fechaFinObj) {
            $q->whereBetween('fecha_hora_entrada', [$fechaInicioObj, $fechaFinObj]);
        }])->orderByDesc('visitas_count')->get();
    }
}
