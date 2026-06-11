<?php

namespace App\Services;

use App\Models\Ciudad;

class CiudadService
{
    public function listar()
    {
        return Ciudad::with('municipio')->orderBy('nombre')->get();
    }

    public function listarPorMunicipio($municipioId)
    {
        return Ciudad::where('municipio_id', $municipioId)->get();
    }

    public function crear(array $datos)
    {
        return Ciudad::create($datos);
    }

    public function actualizar($id, array $datos)
    {
        $ciudad = Ciudad::findOrFail($id);
        $ciudad->update($datos);
        return $ciudad;
    }

    public function eliminar($id)
    {
        return Ciudad::findOrFail($id)->delete();
    }
}
