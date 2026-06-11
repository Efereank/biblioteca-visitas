<?php

namespace App\Services;

use App\Models\SubcategoriaInteres;
use App\Models\PerfilInteres;

class SubcategoriaInteresService
{
    public function listar()
    {
        return SubcategoriaInteres::with('perfil')->orderBy('nombre')->get();
    }

    public function listarPorPerfil($perfilId)
    {
        return SubcategoriaInteres::where('perfil_interes_id', $perfilId)->get();
    }

    public function crear(array $datos)
    {
        return SubcategoriaInteres::create($datos);
    }

    public function actualizar($id, array $datos)
    {
        $subcategoria = SubcategoriaInteres::findOrFail($id);
        $subcategoria->update($datos);
        return $subcategoria;
    }

    public function eliminar($id)
    {
        return SubcategoriaInteres::findOrFail($id)->delete();
    }
}
