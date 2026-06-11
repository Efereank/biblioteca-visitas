<?php

namespace App\Services;

use App\Models\Municipio;

class MunicipioService
{
    public function listar()
    {
        return Municipio::withCount(['parroquias', 'ciudades'])->orderBy('nombre')->get();
    }

    public function crear(array $datos)
    {
        return Municipio::create($datos);
    }

    public function actualizar($id, array $datos)
    {
        $municipio = Municipio::findOrFail($id);
        $municipio->update($datos);
        return $municipio;
    }

    public function eliminar($id)
    {
        return Municipio::findOrFail($id)->delete();
    }
}
