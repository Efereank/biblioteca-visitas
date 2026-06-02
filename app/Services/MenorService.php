<?php

namespace App\Services;

use App\Models\Visitante;
use App\Models\Visita;
use Illuminate\Support\Facades\Validator;

class MenorService
{
    /**
     * Validar los datos del representante según el parentesco.
     */
    public function validarRepresentante(array $datos): array
    {
        $errores = [];

        if (empty($datos['representante_parentesco'])) {
            $errores[] = 'Seleccione el parentesco del representante.';
            return $errores;
        }

        if ($datos['representante_parentesco'] === 'Docente') {
            if (empty($datos['docente_id'])) {
                $errores[] = 'Debe buscar y seleccionar un docente válido.';
            }
        } else {
            if (empty($datos['representante_nombre'])) {
                $errores[] = 'El nombre del representante es obligatorio.';
            }
            if (empty($datos['representante_cedula'])) {
                $errores[] = 'La cédula del representante es obligatoria.';
            }
        }

        return $errores;
    }

    /**
     * Preparar los datos del visitante menor de edad.
     */
    public function prepararDatosMenor(array $datos, int $usuarioId): array
    {
        $datos['tipo_documento'] = 'Sin Identificación';
        $datos['cedula'] = null;
        $datos['fecha_registro'] = now();
        $datos['usuario_registrador_id'] = $usuarioId;

        // Convertir docente_id vacío a null
        if (empty($datos['docente_id'])) {
            $datos['docente_id'] = null;
        }

        return $datos;
    }

    /**
     * Generar código temporal para un menor sin identificación.
     */
    public function generarCodigoTemporal(string $nombres, string $apellidos): string
    {
        return Visitante::generarCodigoTemporal($nombres, $apellidos);
    }

    /**
     * Buscar los menores asociados a un representante.
     */
    public function obtenerMenoresACargo(Visitante $representante): array
    {
        $menores = Visitante::where(function ($query) use ($representante) {
                $query->where('docente_id', $representante->id);
                if (!empty($representante->cedula)) {
                    $query->orWhere('representante_cedula', $representante->cedula);
                }
            })
            ->get();

        return $menores->toArray();
    }

    /**
     * Contar menores con visita activa de un representante.
     */
    public function contarMenoresActivos(Visitante $representante): int
    {
        return Visita::whereHas('visitante', function ($query) use ($representante) {
                $query->where('docente_id', $representante->id);
                if (!empty($representante->cedula)) {
                    $query->orWhere('representante_cedula', $representante->cedula);
                }
            })
            ->whereNull('fecha_hora_salida')
            ->count();
    }
}
