<?php

namespace App\Http\Controllers;

use App\Models\TipoVisitante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TipoVisitanteController extends Controller
{
    public function index()
    {
        $items = TipoVisitante::all();
        return view('admin.tipos-visitante', compact('items'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:50|unique:tipos_visitante,nombre',
            'color' => 'required|string|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item = TipoVisitante::create($request->only('nombre', 'color'));
        return response()->json(['message' => 'Creado exitosamente', 'item' => $item]);
    }

    public function update(Request $request, $id)
    {
        $item = TipoVisitante::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:50|unique:tipos_visitante,nombre,' . $id,
            'color' => 'required|string|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item->update($request->only('nombre', 'color'));
        return response()->json(['message' => 'Actualizado exitosamente']);
    }

    public function destroy($id)
    {
        $item = TipoVisitante::findOrFail($id);

        if ($item->visitantes()->exists()) {
            return response()->json(['message' => 'No se puede eliminar, tiene visitantes asociados'], 422);
        }

        $item->delete();
        return response()->json(['message' => 'Eliminado exitosamente']);
    }
}
