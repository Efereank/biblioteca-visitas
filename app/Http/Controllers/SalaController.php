<?php

namespace App\Http\Controllers;

use App\Models\Sala;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SalaController extends Controller
{
    public function index()
    {
        $items = Sala::all();
        return view('admin.salas', compact('items'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:salas,nombre',
            'descripcion' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item = Sala::create($request->only('nombre', 'descripcion'));
        return response()->json(['message' => 'Creado exitosamente', 'item' => $item]);
    }

    public function update(Request $request, $id)
    {
        $item = Sala::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:salas,nombre,' . $id,
            'descripcion' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item->update($request->only('nombre', 'descripcion'));
        return response()->json(['message' => 'Actualizado exitosamente']);
    }

    public function destroy($id)
    {
        $item = Sala::findOrFail($id);

        if ($item->visitas()->exists()) {
            return response()->json(['message' => 'No se puede eliminar, tiene visitas asociadas'], 422);
        }

        $item->delete();
        return response()->json(['message' => 'Eliminado exitosamente']);
    }
}
