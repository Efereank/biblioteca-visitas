<?php

namespace App\Http\Controllers;

use App\Models\Visita;
use App\Models\Actividad;
use Carbon\Carbon;

class ReporteController extends Controller
{
    public function index()
    {
        // Usar zona horaria de Venezuela
        $now = Carbon::now('America/Caracas');
        $hace30Dias = Carbon::now('America/Caracas')->subDays(30);

        // Obtener todas las actividades
        $actividades = Actividad::all();
        $actividadesLabels = $actividades->pluck('nombre')->toArray();
        $actividadesData = [];

        // Contar manualmente cada actividad
        foreach ($actividades as $actividad) {
            $count = 0;
            $visitas = Visita::whereNotNull('actividades_ids')->get();

            foreach ($visitas as $visita) {
                if (is_array($visita->actividades_ids) && in_array($actividad->id, $visita->actividades_ids)) {
                    $count++;
                }
            }
            $actividadesData[] = $count;
        }

        // Datos para flujo horario
        $flujoHorario = array_fill(0, 24, 0);
        $horasLabels = [];

        for ($i = 0; $i < 24; $i++) {
            $horasLabels[] = sprintf('%02d:00', $i);
        }

        $visitasPorHora = Visita::whereBetween('fecha_hora_entrada', [$hace30Dias, $now])
            ->get()
            ->groupBy(function($visita) {
                return $visita->fecha_hora_entrada->format('H');
            });

        foreach ($visitasPorHora as $hora => $visitas) {
            $flujoHorario[intval($hora)] = $visitas->count();
        }

        // Datos por día de la semana
        $diasLabels = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $diasData = array_fill(0, 7, 0);

        $visitasPorDia = Visita::whereBetween('fecha_hora_entrada', [$hace30Dias, $now])
            ->get()
            ->groupBy(function($visita) {
                return $visita->fecha_hora_entrada->dayOfWeek;
            });

        foreach ($visitasPorDia as $dia => $visitas) {
            $diasData[$dia] = $visitas->count();
        }

        return view('reportes.index', compact(
            'actividadesLabels',
            'actividadesData',
            'horasLabels',
            'flujoHorario',
            'diasLabels',
            'diasData'
        ));
    }
}
