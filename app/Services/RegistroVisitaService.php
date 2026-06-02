<?php

namespace App\Services;

use App\Models\Visitante;
use App\Models\Visita;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class RegistroVisitaService
{
    protected $menorService;

    public function __construct(MenorService $menorService)
    {
        $this->menorService = $menorService;
    }

    /**
     * Registrar una visita completa (visitante nuevo o existente + visita).
     */
    public function registrar(array $datos, $usuario): array
    {
        $visitanteId = $datos['visitante_id'] ?? null;
        $esNuevo = isset($datos['visitante_nuevo']);
        $esMenor = ($datos['visitante_nuevo']['tipo_documento'] ?? '') === 'Sin Identificación';

        // 1. Crear o encontrar visitante
        if ($esNuevo) {
            $visitante = $this->crearVisitante($datos['visitante_nuevo'], $usuario->id);
            $visitanteId = $visitante->id;
        } else {
            $visitante = Visitante::findOrFail($visitanteId);
        }

        // 2. Crear la visita
        $visita = Visita::create([
            'visitante_id' => $visitanteId,
            'proposito_id' => $datos['proposito_id'],
            'sala_id' => $datos['sala_id'] ?? null,
            'fecha_hora_entrada' => Carbon::now('America/Caracas'),
            'observaciones' => $datos['observaciones'] ?? null,
            'actividades_ids' => $datos['actividades_ids'] ?? null,
        ]);

        // 3. Si es bibliotecario en sala, registrar menores automáticamente
        $menoresRegistrados = 0;
        $isBibliotecario = $usuario->isBibliotecario();
        $tieneSala = !empty($datos['sala_id']);

        if ($isBibliotecario && $tieneSala && $visitante) {
            $menoresRegistrados = $this->registrarMenoresEnSala($visitante, $datos);
        }

        return [
            'visita' => $visita,
            'visitante' => $visitante,
            'menores_registrados' => $menoresRegistrados,
        ];
    }

    /**
     * Crear un nuevo visitante.
     */
    private function crearVisitante(array $datos, int $usuarioId): Visitante
    {
        // Generar código temporal si no tiene cédula
        if (empty($datos['cedula'])) {
            $datos['cedula'] = $this->menorService->generarCodigoTemporal(
                $datos['nombres'],
                $datos['apellidos']
            );
        }

        // Limpiar campos que no aplican
        if (empty($datos['docente_id'])) {
            $datos['docente_id'] = null;
        }

        if ($datos['tipo_documento'] !== 'Sin Identificación') {
            $datos['representante_nombre'] = null;
            $datos['representante_cedula'] = null;
            $datos['representante_parentesco'] = null;
            $datos['docente_id'] = null;
        }

        $datos['fecha_registro'] = now();
        $datos['usuario_registrador_id'] = $usuarioId;

        return Visitante::create($datos);
    }

    /**
     * Registrar automáticamente los menores a cargo en la misma sala.
     */
    private function registrarMenoresEnSala(Visitante $representante, array $datos): int
    {
        $menores = $this->menorService->obtenerMenoresACargo($representante);
        $registrados = 0;

        foreach ($menores as $menor) {
            // Verificar que no tenga ya una visita activa en esta sala
            $visitaActiva = Visita::where('visitante_id', $menor['id'])
                ->where('sala_id', $datos['sala_id'])
                ->whereNull('fecha_hora_salida')
                ->first();

            if (!$visitaActiva) {
                Visita::create([
                    'visitante_id' => $menor['id'],
                    'proposito_id' => $datos['proposito_id'],
                    'sala_id' => $datos['sala_id'],
                    'fecha_hora_entrada' => Carbon::now('America/Caracas'),
                    'observaciones' => 'Registro automático por representante',
                    'actividades_ids' => $datos['actividades_ids'] ?? null,
                ]);
                $registrados++;
            }
        }

        return $registrados;
    }
}
