<?php

namespace App\Services;

use App\Models\Parroquia;
use App\Models\Municipio;

class ParroquiaService
{
    public function listar()
    {
        return Parroquia::with('municipio')->orderBy('nombre')->get();
    }

    public function listarPorMunicipio($municipioId)
    {
        return Parroquia::where('municipio_id', $municipioId)->get();
    }

    public function crear(array $datos)
    {
        return Parroquia::create($datos);
    }

    public function actualizar($id, array $datos)
    {
        $parroquia = Parroquia::findOrFail($id);
        $parroquia->update($datos);
        return $parroquia;
    }

    public function eliminar($id)
    {
        return Parroquia::findOrFail($id)->delete();
    }
}
