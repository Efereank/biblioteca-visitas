<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Visita;
use App\Models\Sala;
use App\Models\TipoVisitante;
use App\Models\PropositoVisita;
use Carbon\Carbon;
use App\Exports\ReportesExport;
use FPDF as GlobalFPDF;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Fpdf\Fpdf as FPDF;


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

    // ========== CONSULTAS A LA BASE DE DATOS (LAS MISMAS QUE TENÍAS) ==========
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
        ->groupBy('fecha')->orderByDesc('total')->first();

    $horaPico = Visita::whereBetween('fecha_hora_entrada', [$fechaInicioObj, $fechaFinObj])
        ->select(DB::raw('HOUR(fecha_hora_entrada) as hora'), DB::raw('COUNT(*) as total'))
        ->groupBy('hora')->orderByDesc('total')->first();

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
        ->groupBy('fecha')->orderByDesc('total')->limit(5)->get();

    // ========== CREAR PDF CON FPDF ==========
                $pdf = new GlobalFPDF('P', 'mm', 'A4');
    $pdf->AddPage();

    // Función para convertir texto a ISO-8859-1 (para acentos y ñ)
    $_ = function ($texto) {
        return utf8_decode($texto ?? '');
    };

    // Logo
    $logoPath = public_path('img/logo2.png');
    if (file_exists($logoPath)) {
        $pdf->Image($logoPath, 80, 10, 50);
    }
    $pdf->Ln(35);

    // Título
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->Cell(0, 10, $_('Biblioteca Pública del Zulia "María Calcaño"'), 0, 1, 'C');
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 8, $_('Reporte de Visitas'), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 6, $_('Período: ') . Carbon::parse($fechaInicio)->format('d/m/Y') . ' - ' . Carbon::parse($fechaFin)->format('d/m/Y'), 0, 1, 'C');
    $pdf->Ln(4);

    // Resumen General
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, $_('Resumen General'), 0, 1, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(95, 7, $_('Total de visitas registradas: ') . $totalVisitas, 0, 0);
    $pdf->Cell(95, 7, $_('Promedio diario: ') . $promedioDiario, 0, 1);
    $pdf->Cell(95, 7, $_('Día más concurrido: ') . ($diaPico ? Carbon::parse($diaPico->fecha)->format('d/m/Y') . ' (' . $diaPico->total . ')' : $_('N/D')), 0, 0);
    $pdf->Cell(95, 7, $_('Hora pico: ') . ($horaPico ? sprintf('%02d:00', $horaPico->hora) . ' (' . $horaPico->total . ')' : $_('N/D')), 0, 1);
    $pdf->Cell(95, 7, $_('Duración promedio de visita: ') . round($duracionPromedio) . ' min', 0, 0);
    $pdf->Cell(95, 7, $_('Horas totales de uso: ') . $horasTotales . ' h', 0, 1);
    $pdf->Cell(95, 7, $_('Nuevos visitantes: ') . $nuevosVisitas . ' (' . $pctNuevos . '%)', 0, 0);
    $pdf->Cell(95, 7, $_('Recurrentes: ') . $recurrentesVisitas . ' (' . $pctRecurrentes . '%)', 0, 1);
    $pdf->Ln(4);

    // Función para dibujar tablas
    $drawTable = function ($header, $data, $widths, $aligns = null) use ($pdf, $_) {
        $pdf->SetFont('Arial', 'B', 9);
        foreach ($header as $i => $col) {
            $pdf->Cell($widths[$i], 7, $_($col), 1, 0, 'C');
        }
        $pdf->Ln();
        $pdf->SetFont('Arial', '', 9);
        foreach ($data as $row) {
            foreach ($row as $i => $cell) {
                $align = $aligns[$i] ?? 'L';
                $pdf->Cell($widths[$i], 6, is_numeric($cell) ? $cell : $_($cell), 1, 0, $align);
            }
            $pdf->Ln();
        }
        $pdf->Ln(4);
    };

    // Tabla Salas
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, $_('Distribución por Sala'), 0, 1, 'L');
    $header = ['Sala', 'Visitas', '%'];
    $data = $salas->map(function ($s) use ($totalVisitas) {
        $pct = $totalVisitas > 0 ? number_format(($s->visitas_count / $totalVisitas) * 100, 1) : '0.0';
        return [$s->nombre, $s->visitas_count, $pct . '%'];
    })->toArray();
    $drawTable($header, $data, [90, 50, 50], ['L', 'C', 'R']);

    // Tabla Tipos de Visitante
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, $_('Distribución por Tipo de Visitante'), 0, 1, 'L');
    $data = $tipos->map(function ($t) use ($totalVisitas) {
        $pct = $totalVisitas > 0 ? number_format(($t->visitas_count / $totalVisitas) * 100, 1) : '0.0';
        return [$t->nombre, $t->visitas_count, $pct . '%'];
    })->toArray();
    $drawTable($header, $data, [90, 50, 50], ['L', 'C', 'R']);

    // Tabla Propósitos
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, $_('Distribución por Propósito'), 0, 1, 'L');
    $data = $propositos->map(function ($p) use ($totalVisitas) {
        $pct = $totalVisitas > 0 ? number_format(($p->visitas_count / $totalVisitas) * 100, 1) : '0.0';
        return [$p->nombre, $p->visitas_count, $pct . '%'];
    })->toArray();
    $drawTable($header, $data, [90, 50, 50], ['L', 'C', 'R']);

    // Tabla Género
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, $_('Distribución por Género'), 0, 1, 'L');
    $data = $visitasPorGenero->map(function ($g) use ($totalVisitas) {
        $pct = $totalVisitas > 0 ? number_format(($g->total / $totalVisitas) * 100, 1) : '0.0';
        return [$g->genero, $g->total, $pct . '%'];
    })->toArray();
    $drawTable($header, $data, [90, 50, 50], ['L', 'C', 'R']);

    // Tabla Grupo Etario
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, $_('Distribución por Grupo Etario'), 0, 1, 'L');
    $data = $visitasPorEdad->map(function ($e) use ($totalVisitas) {
        $pct = $totalVisitas > 0 ? number_format(($e->total / $totalVisitas) * 100, 1) : '0.0';
        return [$e->grupo_etario, $e->total, $pct . '%'];
    })->toArray();
    $drawTable($header, $data, [90, 50, 50], ['L', 'C', 'R']);

    // Top 5 días
    if ($topDias->isNotEmpty()) {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, $_('Top 5 Días con Mayor Afluencia'), 0, 1, 'L');
        $header = ['#', 'Fecha', 'Día', 'Visitas'];
        $data = $topDias->map(function ($d, $i) {
            $fecha = Carbon::parse($d->fecha);
            $diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            return [
                $i + 1,
                $fecha->format('d/m/Y'),
                $diasSemana[$fecha->dayOfWeek],
                $d->total
            ];
        })->toArray();
        $drawTable($header, $data, [10, 45, 45, 30], ['C', 'L', 'L', 'C']);
    }

    // Pie de página
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->Cell(0, 5, $_('Documento generado el ') . now()->format('d/m/Y H:i') . ' - ' . $_('Sistema de Gestión Biblioteca "María Calcaño"'), 0, 1, 'C');

    // Salida
    return response($pdf->Output('S'), 200)
        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'attachment; filename="reporte_biblioteca_' . date('Y-m-d') . '.pdf"');
}
}
