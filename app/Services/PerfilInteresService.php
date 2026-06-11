<?php

namespace App\Services;

use App\Models\PerfilInteres;

class PerfilInteresService
{
    public function listar()
    {
        return PerfilInteres::with('subcategorias')->orderBy('nombre')->get();
    }

    public function crear(array $datos)
    {
        return PerfilInteres::create($datos);
    }

    public function actualizar($id, array $datos)
    {
        $perfil = PerfilInteres::findOrFail($id);
        $perfil->update($datos);
        return $perfil;
    }

    public function eliminar($id)
    {
        return PerfilInteres::findOrFail($id)->delete();
    }
}
