<?php

namespace App\Http\Controllers;

use App\Models\Municipio;
use Illuminate\Http\Request;

class MunicipioController extends Controller
{
    public function index()
    {
        $municipios = Municipio::withCount(['parroquias', 'ciudades'])->orderBy('nombre')->get();
        return view('admin.municipios.index', compact('municipios'));
    }

    public function create()
    {
        return view('admin.municipios.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:municipios',
            'capital' => 'nullable|string|max:100',
        ]);

        Municipio::create($request->all());

        return redirect()->route('municipios.index')->with('success', 'Municipio creado correctamente.');
    }

    public function edit(Municipio $municipio)
    {
        return view('admin.municipios.edit', compact('municipio'));
    }

    public function update(Request $request, Municipio $municipio)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:municipios,nombre,' . $municipio->id,
            'capital' => 'nullable|string|max:100',
        ]);

        $municipio->update($request->all());

        return redirect()->route('municipios.index')->with('success', 'Municipio actualizado correctamente.');
    }

    public function destroy(Municipio $municipio)
    {
        $municipio->delete();
        return redirect()->route('municipios.index')->with('success', 'Municipio eliminado correctamente.');
    }
}
