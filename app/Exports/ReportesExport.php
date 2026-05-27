<?php

namespace App\Exports;

use App\Models\Visita;
use App\Models\Sala;
use App\Models\TipoVisitante;
use App\Models\PropositoVisita;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportesExport implements WithMultipleSheets
{
    protected Carbon $fechaInicio;
    protected Carbon $fechaFin;

    public function __construct($fechaInicio, $fechaFin)
    {
        $this->fechaInicio = Carbon::parse($fechaInicio)->startOfDay();
        $this->fechaFin = Carbon::parse($fechaFin)->endOfDay();
    }

    public function sheets(): array
    {
        return [
            new VisitasSheet($this->fechaInicio, $this->fechaFin),
            new SalasSheet($this->fechaInicio, $this->fechaFin),
            new TiposVisitanteSheet($this->fechaInicio, $this->fechaFin),
            new PropositosSheet($this->fechaInicio, $this->fechaFin),
            new FidelizacionSheet($this->fechaInicio, $this->fechaFin),
            new GeneroSheet($this->fechaInicio, $this->fechaFin),
            new GrupoEtarioSheet($this->fechaInicio, $this->fechaFin),
            new TopDiasSheet($this->fechaInicio, $this->fechaFin),
        ];
    }
}

// ====== HOJA: VISITAS (detalle) ======
class VisitasSheet implements FromQuery, WithHeadings, WithMapping, WithTitle
{
    protected Carbon $fi, $ff;

    public function __construct(Carbon $fi, Carbon $ff)
    {
        $this->fi = $fi;
        $this->ff = $ff;
    }

    public function query()
    {
        return Visita::with(['visitante.tipoVisitante', 'proposito', 'sala'])
            ->whereBetween('fecha_hora_entrada', [$this->fi, $this->ff])
            ->orderBy('fecha_hora_entrada');
    }

    public function headings(): array
    {
        return ['ID', 'Visitante', 'Cédula', 'Tipo Visitante', 'Sala', 'Propósito', 'Entrada', 'Salida', 'Observaciones'];
    }

    public function map($visita): array
    {
        return [
            $visita->id,
            $visita->visitante->nombre_completo ?? '',
            $visita->visitante->cedula ?? '',
            $visita->visitante->tipoVisitante->nombre ?? '',
            $visita->sala->nombre ?? 'Sin sala',
            $visita->proposito->nombre ?? '',
            $visita->fecha_hora_entrada->format('d/m/Y H:i'),
            $visita->fecha_hora_salida ? $visita->fecha_hora_salida->format('d/m/Y H:i') : 'Activo',
            $visita->observaciones ?? '',
        ];
    }

    public function title(): string
    {
        return 'Visitas';
    }
}

// ====== HOJA: SALAS ======
class SalasSheet implements FromCollection, WithHeadings, WithTitle
{
    protected Carbon $fi, $ff;

    public function __construct(Carbon $fi, Carbon $ff)
    {
        $this->fi = $fi;
        $this->ff = $ff;
    }

    public function collection()
    {
        return Sala::withCount(['visitas' => function ($q) {
            $q->whereBetween('fecha_hora_entrada', [$this->fi, $this->ff]);
        }])->get()->map(function ($sala) {
            return [
                'Sala' => $sala->nombre,
                'Total Visitas' => $sala->visitas_count,
            ];
        });
    }

    public function headings(): array
    {
        return ['Sala', 'Total Visitas'];
    }

    public function title(): string
    {
        return 'Salas';
    }
}

// ====== HOJA: TIPOS DE VISITANTE ======
class TiposVisitanteSheet implements FromCollection, WithHeadings, WithTitle
{
    protected Carbon $fi, $ff;

    public function __construct(Carbon $fi, Carbon $ff)
    {
        $this->fi = $fi;
        $this->ff = $ff;
    }

    public function collection()
    {
        return TipoVisitante::leftJoin('visitantes', 'tipos_visitante.id', '=', 'visitantes.tipo_visitante_id')
            ->leftJoin('visitas', function ($join) {
                $join->on('visitantes.id', '=', 'visitas.visitante_id')
                     ->whereBetween('visitas.fecha_hora_entrada', [$this->fi, $this->ff]);
            })
            ->select('tipos_visitante.nombre', DB::raw('COUNT(visitas.id) as total'))
            ->groupBy('tipos_visitante.id', 'tipos_visitante.nombre')
            ->orderBy('tipos_visitante.nombre')
            ->get()
            ->map(function ($item) {
                return [
                    'Tipo Visitante' => $item->nombre,
                    'Cantidad Visitas' => $item->total,
                ];
            });
    }

    public function headings(): array
    {
        return ['Tipo Visitante', 'Cantidad Visitas'];
    }

    public function title(): string
    {
        return 'Tipos Visitante';
    }
}

// ====== HOJA: PROPÓSITOS ======
class PropositosSheet implements FromCollection, WithHeadings, WithTitle
{
    protected Carbon $fi, $ff;

    public function __construct(Carbon $fi, Carbon $ff)
    {
        $this->fi = $fi;
        $this->ff = $ff;
    }

    public function collection()
    {
        return PropositoVisita::withCount(['visitas' => function ($q) {
            $q->whereBetween('fecha_hora_entrada', [$this->fi, $this->ff]);
        }])->get()->map(function ($proposito) {
            return [
                'Propósito' => $proposito->nombre,
                'Cantidad Visitas' => $proposito->visitas_count,
            ];
        });
    }

    public function headings(): array
    {
        return ['Propósito', 'Cantidad Visitas'];
    }

    public function title(): string
    {
        return 'Propósitos';
    }
}

// ====== HOJA: FIDELIZACIÓN (Nuevos vs Recurrentes) ======
class FidelizacionSheet implements FromCollection, WithHeadings, WithTitle
{
    protected Carbon $fi, $ff;

    public function __construct(Carbon $fi, Carbon $ff)
    {
        $this->fi = $fi;
        $this->ff = $ff;
    }

    public function collection()
    {
        $total = Visita::whereBetween('fecha_hora_entrada', [$this->fi, $this->ff])->count();

        $nuevos = Visita::whereBetween('visitas.fecha_hora_entrada', [$this->fi, $this->ff])
            ->join('visitantes', 'visitas.visitante_id', '=', 'visitantes.id')
            ->where('visitantes.fecha_registro', '>=', $this->fi)
            ->where('visitantes.fecha_registro', '<=', $this->ff)
            ->count();

        $recurrentes = $total - $nuevos;
        $pctNuevos = $total > 0 ? round(($nuevos / $total) * 100) : 0;
        $pctRecurrentes = 100 - $pctNuevos;

        return collect([
            ['Tipo de usuario' => 'Usuarios de primera visita', 'Ingresos' => $nuevos, '%' => $pctNuevos],
            ['Tipo de usuario' => 'Usuarios recurrentes', 'Ingresos' => $recurrentes, '%' => $pctRecurrentes],
        ]);
    }

    public function headings(): array
    {
        return ['Tipo de usuario', 'Ingresos', '%'];
    }

    public function title(): string
    {
        return 'Fidelización';
    }
}

// ====== HOJA: DISTRIBUCIÓN POR GÉNERO ======
class GeneroSheet implements FromCollection, WithHeadings, WithTitle
{
    protected Carbon $fi, $ff;

    public function __construct(Carbon $fi, Carbon $ff)
    {
        $this->fi = $fi;
        $this->ff = $ff;
    }

    public function collection()
    {
        $total = Visita::whereBetween('fecha_hora_entrada', [$this->fi, $this->ff])->count() ?: 1;

        return Visita::whereBetween('visitas.fecha_hora_entrada', [$this->fi, $this->ff])
            ->join('visitantes', 'visitas.visitante_id', '=', 'visitantes.id')
            ->select('visitantes.genero', DB::raw('COUNT(*) as total'))
            ->groupBy('visitantes.genero')
            ->get()
            ->map(function ($item) use ($total) {
                return [
                    'Género' => $item->genero ?: 'No especificado',
                    'Ingresos' => $item->total,
                    '%' => number_format(($item->total / $total) * 100, 1),
                ];
            });
    }

    public function headings(): array
    {
        return ['Género', 'Ingresos', '%'];
    }

    public function title(): string
    {
        return 'Género';
    }
}

// ====== HOJA: GRUPO ETARIO ======
class GrupoEtarioSheet implements FromCollection, WithHeadings, WithTitle
{
    protected Carbon $fi, $ff;

    public function __construct(Carbon $fi, Carbon $ff)
    {
        $this->fi = $fi;
        $this->ff = $ff;
    }

    public function collection()
    {
        $total = Visita::whereBetween('fecha_hora_entrada', [$this->fi, $this->ff])->count() ?: 1;

        return Visita::whereBetween('visitas.fecha_hora_entrada', [$this->fi, $this->ff])
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
            ->get()
            ->map(function ($item) use ($total) {
                return [
                    'Grupo etario' => $item->grupo_etario,
                    'Ingresos' => $item->total,
                    '%' => number_format(($item->total / $total) * 100, 1),
                ];
            });
    }

    public function headings(): array
    {
        return ['Grupo etario', 'Ingresos', '%'];
    }

    public function title(): string
    {
        return 'Grupo Etario';
    }
}

// ====== HOJA: TOP 5 DÍAS ======
class TopDiasSheet implements FromCollection, WithHeadings, WithTitle
{
    protected Carbon $fi, $ff;

    public function __construct(Carbon $fi, Carbon $ff)
    {
        $this->fi = $fi;
        $this->ff = $ff;
    }

    public function collection()
    {
        $diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

        return Visita::whereBetween('fecha_hora_entrada', [$this->fi, $this->ff])
            ->select(DB::raw('DATE(fecha_hora_entrada) as fecha'), DB::raw('COUNT(*) as total'))
            ->groupBy('fecha')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(function ($item, $index) use ($diasSemana) {
                $fecha = Carbon::parse($item->fecha);
                return [
                    '#' => $index + 1,
                    'Fecha' => $fecha->format('d/m/Y'),
                    'Día' => $diasSemana[$fecha->dayOfWeek],
                    'Ingresos' => $item->total,
                ];
            });
    }

    public function headings(): array
    {
        return ['#', 'Fecha', 'Día', 'Ingresos'];
    }

    public function title(): string
    {
        return 'Top 5 Días';
    }
}
