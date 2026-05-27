<?php

namespace App\Http\Controllers;

use App\Models\Municipio;
use App\Models\Parroquia;
use App\Models\Ciudad;
use Illuminate\Http\Request;

class UbicacionController extends Controller
{
    public function getParroquias($municipioId)
    {
        $parroquias = Parroquia::where('municipio_id', $municipioId)->get();
        return response()->json($parroquias);
    }

    public function getCiudades($municipioId)
    {
        $ciudades = Ciudad::where('municipio_id', $municipioId)->get();
        return response()->json($ciudades);
    }
}
