<?php

namespace App\Http\Controllers;

use App\Models\Ciudad;
use App\Models\Municipio;
use Illuminate\Http\Request;

class CiudadController extends Controller
{
    public function index()
    {
        $ciudades = Ciudad::with('municipio')->orderBy('nombre')->get();
        return view('admin.ciudades.index', compact('ciudades'));
    }

    public function create()
    {
        $municipios = Municipio::orderBy('nombre')->get();
        return view('admin.ciudades.create', compact('municipios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'municipio_id' => 'required|exists:municipios,id',
        ]);

        Ciudad::create($request->all());

        return redirect()->route('ciudades.index')->with('success', 'Ciudad creada correctamente.');
    }

    public function edit(Ciudad $ciudad)
    {
        $municipios = Municipio::orderBy('nombre')->get();
        return view('admin.ciudades.edit', compact('ciudad', 'municipios'));
    }

    public function update(Request $request, Ciudad $ciudad)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'municipio_id' => 'required|exists:municipios,id',
        ]);

        $ciudad->update($request->all());

        return redirect()->route('ciudades.index')->with('success', 'Ciudad actualizada correctamente.');
    }

    public function destroy(Ciudad $ciudad)
    {
        $ciudad->delete();
        return redirect()->route('ciudades.index')->with('success', 'Ciudad eliminada correctamente.');
    }
}
