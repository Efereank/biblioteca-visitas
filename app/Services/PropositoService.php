<?php

namespace App\Services;

use App\Models\PropositoVisita;

class PropositoService
{
    public function listar()
    {
        return PropositoVisita::orderBy('nombre')->get();
    }

    public function crear(array $datos)
    {
        return PropositoVisita::create($datos);
    }

    public function actualizar($id, array $datos)
    {
        $proposito = PropositoVisita::findOrFail($id);
        $proposito->update($datos);
        return $proposito;
    }

    public function eliminar($id)
    {
        return PropositoVisita::findOrFail($id)->delete();
    }
}
