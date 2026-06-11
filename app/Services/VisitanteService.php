<?php

namespace App\Services;

use App\Models\Visitante;
use Illuminate\Support\Facades\Validator;

class VisitanteService
{
    /**
     * Listar visitantes con paginación y búsqueda.
     */
    public function listar($search = null, $perPage = 9)
    {
        $query = Visitante::with('tipoVisitante');

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('cedula', 'like', "%$search%")
                  ->orWhere('nombres', 'like', "%$search%")
                  ->orWhere('apellidos', 'like', "%$search%");
            });
        }

        return $query->orderBy('visitas_count', 'desc')->paginate($perPage);
    }

    /**
     * Buscar visitante por cédula.
     */
    public function buscarPorCedula($cedula)
    {
        if (empty($cedula) || str_starts_with($cedula, 'TMP-')) {
            return null;
        }

        $cedulaLimpia = preg_replace('/[^0-9]/', '', $cedula);

        return Visitante::with('tipoVisitante')
            ->where('cedula', $cedulaLimpia)
            ->first();
    }

    /**
     * Obtener visitante por ID.
     */
    public function obtenerPorId($id)
    {
        $visitante = Visitante::with('tipoVisitante')->findOrFail($id);
        $visitante->append(['nombre_completo', 'edad', 'es_frecuente']);

        if (!isset($visitante->visitas_count)) {
            $visitante->loadCount('visitas');
        }

        return $visitante;
    }

    /**
     * Validar datos del visitante.
     */
    public function validarDatos(array $datos, $visitanteId = null)
    {
        $reglas = [
            'tipo_documento' => 'nullable|in:C.I.,Pasaporte,Partida de Nacimiento,Sin Identificación,Otro',
            'cedula' => [
                'nullable',
                function ($attribute, $value, $fail) use ($datos, $visitanteId) {
                    $tipo = $datos['tipo_documento'] ?? 'C.I.';
                    if (in_array($tipo, ['C.I.', 'Pasaporte']) && empty($value)) {
                        $fail('El número de documento es obligatorio para ' . $tipo . '.');
                    }
                    if (!empty($value)) {
                        $cedulaLimpia = preg_replace('/[^0-9]/', '', $value);
                        if (strlen($cedulaLimpia) < 7) {
                            $fail('El número de documento debe tener mínimo 7 dígitos.');
                        }
                        $exists = Visitante::where('cedula', $cedulaLimpia)
                            ->when($visitanteId, function($q) use ($visitanteId) {
                                $q->where('id', '!=', $visitanteId);
                            })
                            ->exists();
                        if ($exists) {
                            $fail('Este número de documento ya está registrado.');
                        }
                    }
                },
            ],
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'tipo_visitante_id' => 'required|exists:tipos_visitante,id',
        ];

        return Validator::make($datos, $reglas);
    }

    /**
     * Crear un nuevo visitante.
     */
    public function crear(array $datos, $usuarioId)
    {
        if (empty($datos['cedula'])) {
            $datos['cedula'] = Visitante::generarCodigoTemporal(
                $datos['nombres'],
                $datos['apellidos']
            );
        }

        $datos['fecha_registro'] = now();
        $datos['usuario_registrador_id'] = $usuarioId;

        return Visitante::create($datos);
    }

    /**
     * Actualizar un visitante existente.
     */
    public function actualizar($id, array $datos)
    {
        $visitante = Visitante::findOrFail($id);

        if (empty($datos['cedula']) && empty($visitante->cedula)) {
            $datos['cedula'] = Visitante::generarCodigoTemporal(
                $datos['nombres'] ?? $visitante->nombres,
                $datos['apellidos'] ?? $visitante->apellidos
            );
        }

        $datos['fecha_ultima_modificacion'] = now();
        $visitante->update($datos);

        return $visitante->fresh()->load('tipoVisitante');
    }

    /**
     * Eliminar un visitante y sus visitas.
     */
    public function eliminar($id)
    {
        $visitante = Visitante::findOrFail($id);
        $tieneVisitas = $visitante->visitas()->exists();

        if ($tieneVisitas) {
            $visitante->visitas()->delete();
        }

        $visitante->delete();

        return ['tieneVisitas' => $tieneVisitas];
    }
}
