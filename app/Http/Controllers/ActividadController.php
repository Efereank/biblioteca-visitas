<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ActividadController extends Controller
{
    public function index()
    {
        $items = Actividad::all();
        return view('admin.actividades', compact('items'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100|unique:actividades,nombre'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item = Actividad::create($request->only('nombre'));
        return response()->json(['message' => 'Creado exitosamente', 'item' => $item]);
    }

    public function update(Request $request, $id)
    {
        $item = Actividad::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100|unique:actividades,nombre,' . $id
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item->update($request->only('nombre'));
        return response()->json(['message' => 'Actualizado exitosamente']);
    }

    public function destroy($id)
    {
        $item = Actividad::findOrFail($id);
        $item->delete();
        return response()->json(['message' => 'Eliminado exitosamente']);
    }
}
