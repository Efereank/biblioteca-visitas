<?php

namespace App\Http\Controllers;

use App\Models\Parroquia;
use App\Models\Municipio;
use Illuminate\Http\Request;

class ParroquiaController extends Controller
{
    public function index()
    {
        $parroquias = Parroquia::with('municipio')->orderBy('nombre')->get();
        return view('admin.parroquias.index', compact('parroquias'));
    }

    public function create()
    {
        $municipios = Municipio::orderBy('nombre')->get();
        return view('admin.parroquias.create', compact('municipios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'municipio_id' => 'required|exists:municipios,id',
        ]);

        Parroquia::create($request->all());

        return redirect()->route('parroquias.index')->with('success', 'Parroquia creada correctamente.');
    }

    public function edit(Parroquia $parroquia)
    {
        $municipios = Municipio::orderBy('nombre')->get();
        return view('admin.parroquias.edit', compact('parroquia', 'municipios'));
    }

    public function update(Request $request, Parroquia $parroquia)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'municipio_id' => 'required|exists:municipios,id',
        ]);

        $parroquia->update($request->all());

        return redirect()->route('parroquias.index')->with('success', 'Parroquia actualizada correctamente.');
    }

    public function destroy(Parroquia $parroquia)
    {
        $parroquia->delete();
        return redirect()->route('parroquias.index')->with('success', 'Parroquia eliminada correctamente.');
    }
}
