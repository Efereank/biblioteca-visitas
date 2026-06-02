<?php

namespace App\Services;

use App\Models\Visita;
use App\Models\Visitante;
use Carbon\Carbon;

class CierreVisitaService
{
    protected $menorService;

    public function __construct(MenorService $menorService)
    {
        $this->menorService = $menorService;
    }

    /**
     * Registrar la salida de una visita y cerrar visitas relacionadas.
     */
    public function registrarSalida(int $visitaId, $usuario): array
    {
        $visita = Visita::with('visitante')->findOrFail($visitaId);

        if ($visita->fecha_hora_salida) {
            return [
                'success' => false,
                'mensaje' => 'La visita ya tiene salida registrada',
                'menores_cerrados' => 0,
            ];
        }

        $ahora = Carbon::now('America/Caracas');
        $visita->fecha_hora_salida = $ahora;
        $visita->save();

        $visitante = $visita->visitante;
        $menoresCerrados = 0;

        // Si es recepcionista o admin, cerrar TODAS las visitas activas del visitante
        $esRecepcionistaOAdmin = in_array($usuario->role, ['recepcionista', 'admin']);

        if ($esRecepcionistaOAdmin && $visitante) {
            Visita::where('visitante_id', $visitante->id)
                ->whereNull('fecha_hora_salida')
                ->where('id', '!=', $visita->id)
                ->update(['fecha_hora_salida' => $ahora]);
        }

        // Cerrar visitas de menores a cargo
        if ($visitante) {
            $menoresCerrados = $this->cerrarVisitasMenores($visitante, $ahora);
        }

        // Construir mensaje
        $mensaje = 'Salida registrada correctamente';
        if ($menoresCerrados > 0) {
            $mensaje .= ". También se registró la salida de {$menoresCerrados} menor(es) a cargo.";
        }
        if ($esRecepcionistaOAdmin && $visitante) {
            $mensaje .= ' Se han cerrado todas las visitas activas del visitante.';
        }

        return [
            'success' => true,
            'mensaje' => $mensaje,
            'menores_cerrados' => $menoresCerrados,
        ];
    }

    /**
     * Cerrar las visitas activas de los menores a cargo del representante.
     */
    private function cerrarVisitasMenores(Visitante $representante, Carbon $fechaSalida): int
    {
        $cerrados = 0;

        // 1. Menores por docente_id
        $menoresDocente = Visita::whereHas('visitante', function ($query) use ($representante) {
                $query->where('docente_id', $representante->id);
            })
            ->whereNull('fecha_hora_salida')
            ->get();

        foreach ($menoresDocente as $visitaMenor) {
            $visitaMenor->fecha_hora_salida = $fechaSalida;
            $visitaMenor->save();
            $cerrados++;
        }

        // 2. Menores por representante_cedula
        if (!empty($representante->cedula)) {
            $menoresCedula = Visita::whereHas('visitante', function ($query) use ($representante) {
                    $query->where('representante_cedula', $representante->cedula);
                })
                ->whereNull('fecha_hora_salida')
                ->get();

            foreach ($menoresCedula as $visitaMenor) {
                $visitaMenor->fecha_hora_salida = $fechaSalida;
                $visitaMenor->save();
                $cerrados++;
            }
        }

        return $cerrados;
    }
}
