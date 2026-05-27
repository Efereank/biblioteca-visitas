<?php

namespace App\Http\Controllers;

use App\Models\PropositoVisita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PropositoVisitaController extends Controller
{
    public function index()
    {
        $items = PropositoVisita::all();
        return view('admin.propositos-visita', compact('items'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:50|unique:propositos_visita,nombre',
            'color' => 'required|string|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item = PropositoVisita::create($request->only('nombre', 'color'));
        return response()->json(['message' => 'Creado exitosamente', 'item' => $item]);
    }

    public function update(Request $request, $id)
    {
        $item = PropositoVisita::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:50|unique:propositos_visita,nombre,' . $id,
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
        $item = PropositoVisita::findOrFail($id);

        if ($item->visitas()->exists()) {
            return response()->json(['message' => 'No se puede eliminar, tiene visitas asociadas'], 422);
        }

        $item->delete();
        return response()->json(['message' => 'Eliminado exitosamente']);
    }
}
