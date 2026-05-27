<?php

namespace App\Http\Controllers;

use App\Models\PerfilInteres;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PerfilInteresController extends Controller
{
    public function index()
    {
        $perfiles = PerfilInteres::withCount('subcategorias')->get();
        return view('admin.perfiles-interes', compact('perfiles'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100|unique:perfiles_interes,nombre'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $perfil = PerfilInteres::create(['nombre' => $request->nombre]);
        return response()->json(['message' => 'Perfil creado', 'perfil' => $perfil]);
    }

    public function update(Request $request, $id)
    {
        $perfil = PerfilInteres::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100|unique:perfiles_interes,nombre,' . $id
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $perfil->update(['nombre' => $request->nombre]);
        return response()->json(['message' => 'Perfil actualizado']);
    }

    public function destroy($id)
    {
        $perfil = PerfilInteres::findOrFail($id);

        if ($perfil->subcategorias()->exists()) {
            return response()->json(['message' => 'No se puede eliminar, tiene subcategorías asociadas'], 422);
        }

        $perfil->delete();
        return response()->json(['message' => 'Perfil eliminado']);
    }
}
