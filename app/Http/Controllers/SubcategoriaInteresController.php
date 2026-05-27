<?php

namespace App\Http\Controllers;

use App\Models\SubcategoriaInteres;
use App\Models\PerfilInteres;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubcategoriaInteresController extends Controller
{

public function index(Request $request)
{
    $perfilId = $request->query('perfil_id');
    $subcategorias = SubcategoriaInteres::with('perfil')
        ->when($perfilId, fn($q) => $q->where('perfil_interes_id', $perfilId))
        ->when(!$perfilId, fn($q) => $q->take(10)) // Solo limita cuando NO hay filtro
        ->get();

    if ($request->wantsJson()) {
        return response()->json($subcategorias);
    }

    $perfiles = PerfilInteres::all();
    return view('admin.subcategorias-interes', compact('subcategorias', 'perfiles', 'perfilId'));
}

public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'nombre' => 'required|string|max:150',
        'perfil_interes_id' => 'required|exists:perfiles_interes,id'
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Validar que no exista duplicado
    $existe = SubcategoriaInteres::where('nombre', $request->nombre)
        ->where('perfil_interes_id', $request->perfil_interes_id)
        ->exists();

    if ($existe) {
        return response()->json(['errors' => ['nombre' => ['Esta subcategoría ya existe en este perfil']]], 422);
    }

    $sub = SubcategoriaInteres::create($request->only('nombre', 'perfil_interes_id'));
    return response()->json(['message' => 'Subcategoría creada', 'subcategoria' => $sub]);
}

public function update(Request $request, $id)
{
    $sub = SubcategoriaInteres::findOrFail($id);

    $validator = Validator::make($request->all(), [
        'nombre' => 'required|string|max:150',
        'perfil_interes_id' => 'required|exists:perfiles_interes,id'
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Validar que no exista duplicado (excluyendo el registro actual)
    $existe = SubcategoriaInteres::where('nombre', $request->nombre)
        ->where('perfil_interes_id', $request->perfil_interes_id)
        ->where('id', '!=', $id)
        ->exists();

    if ($existe) {
        return response()->json(['errors' => ['nombre' => ['Esta subcategoría ya existe en este perfil']]], 422);
    }

    $sub->update($request->only('nombre', 'perfil_interes_id'));
    return response()->json(['message' => 'Subcategoría actualizada']);
}

    public function destroy($id)
    {
        $sub = SubcategoriaInteres::findOrFail($id);
        $sub->delete();
        return response()->json(['message' => 'Subcategoría eliminada']);
    }
}
