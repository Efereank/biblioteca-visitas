<?php

namespace App\Services;

use App\Models\Visita;
use App\Models\Visitante;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class VisitaService
{
    /**
     * Verificar si un visitante tiene una visita activa.
     */
    public function verificarVisitaActiva($visitanteId)
    {
        $visitaActiva = Visita::where('visitante_id', $visitanteId)
            ->whereNull('fecha_hora_salida')
            ->first();

        return [
            'tieneVisitaActiva' => !is_null($visitaActiva),
            'visita' => $visitaActiva
        ];
    }

    /**
     * Listar historial de visitas con filtros.
     */
    public function historial($filtros = [], $user = null)
    {
        $query = Visita::with(['visitante', 'proposito', 'sala'])
            ->orderBy('fecha_hora_entrada', 'desc');

        // Bibliotecario solo ve sus salas
        if ($user && method_exists($user, 'isBibliotecario') && $user->isBibliotecario()) {
            $salasIds = $user->salas->pluck('id');
            $query->whereIn('sala_id', $salasIds);
        }

        if (!empty($filtros['cedula'])) {
            $query->whereHas('visitante', function($q) use ($filtros) {
                $q->where('cedula', 'like', "%{$filtros['cedula']}%");
            });
        }

        if (!empty($filtros['fecha_inicio'])) {
            $query->whereDate('fecha_hora_entrada', '>=', $filtros['fecha_inicio']);
        }

        if (!empty($filtros['fecha_fin'])) {
            $query->whereDate('fecha_hora_entrada', '<=', $filtros['fecha_fin']);
        }

        if (!empty($filtros['proposito'])) {
            $query->where('proposito_id', $filtros['proposito']);
        }

        if (!empty($filtros['sala'])) {
            $query->where('sala_id', $filtros['sala']);
        }

        if (!empty($filtros['estado'])) {
            if ($filtros['estado'] == 'activo') {
                $query->whereNull('fecha_hora_salida');
            } elseif ($filtros['estado'] == 'finalizado') {
                $query->whereNotNull('fecha_hora_salida');
            }
        }

        return $query->paginate(10);
    }

    /**
     * Obtener todas las visitas en un rango de fechas.
     */
    public function obtenerVisitasEnRango($fechaInicio, $fechaFin)
    {
        return Visita::with(['visitante', 'proposito', 'sala'])
            ->whereBetween('fecha_hora_entrada', [$fechaInicio, $fechaFin])
            ->orderBy('fecha_hora_entrada')
            ->get();
    }
}
