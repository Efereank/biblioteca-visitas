<?php

namespace App\Services;

use App\Models\Sala;

class SalaService
{
    public function listar()
    {
        return Sala::orderBy('nombre')->get();
    }

    public function crear(array $datos)
    {
        return Sala::create($datos);
    }

    public function actualizar($id, array $datos)
    {
        $sala = Sala::findOrFail($id);
        $sala->update($datos);
        return $sala;
    }

    public function eliminar($id)
    {
        return Sala::findOrFail($id)->delete();
    }
}
