<?php

namespace App\Services;

use App\Models\Actividad;

class ActividadService
{
    public function listar()
    {
        return Actividad::orderBy('nombre')->get();
    }

    public function crear(array $datos)
    {
        return Actividad::create($datos);
    }

    public function actualizar($id, array $datos)
    {
        $actividad = Actividad::findOrFail($id);
        $actividad->update($datos);
        return $actividad;
    }

    public function eliminar($id)
    {
        return Actividad::findOrFail($id)->delete();
    }
}
