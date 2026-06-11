<?php

namespace App\Services;

use App\Models\TipoVisitante;

class TipoVisitanteService
{
    public function listar()
    {
        return TipoVisitante::orderBy('nombre')->get();
    }

    public function crear(array $datos)
    {
        return TipoVisitante::create($datos);
    }

    public function actualizar($id, array $datos)
    {
        $tipo = TipoVisitante::findOrFail($id);
        $tipo->update($datos);
        return $tipo;
    }

    public function eliminar($id)
    {
        return TipoVisitante::findOrFail($id)->delete();
    }
}
