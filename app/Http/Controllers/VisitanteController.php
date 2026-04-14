<?php

namespace App\Http\Controllers;

use App\Models\Visitante;
use App\Models\TipoVisitante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VisitanteController extends Controller
{
    public function index(Request $request)
    {
        $query = Visitante::with('tipoVisitante');

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('cedula', 'like', "%$search%")
                  ->orWhere('nombres', 'like', "%$search%")
                  ->orWhere('apellidos', 'like', "%$search%");
            });
        }

        $visitantes = $query->orderBy('visitas_count', 'desc')->paginate(9);
        return view('visitantes.index', compact('visitantes'));
    }
public function searchByCedula($cedula)
{
    // Limpiar la cédula: solo números
    $cedulaLimpia = preg_replace('/[^0-9]/', '', $cedula);

    // Buscar por la cédula limpia
    $visitante = Visitante::with('tipoVisitante')
        ->where('cedula', $cedulaLimpia)
        ->first();

    if ($visitante) {
        return response()->json($visitante);
    }

    return response()->json(['message' => 'Visitante no encontrado'], 404);
}

    public function show($id)
    {
        $visitante = Visitante::with('tipoVisitante')->findOrFail($id);
        return response()->json($visitante);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cedula' => [
                'required',
                'unique:visitantes,cedula',
                function ($attribute, $value, $fail) {
                    $cedulaLimpia = preg_replace('/[^0-9]/', '', $value);
                    if (strlen($cedulaLimpia) < 7) {
                        $fail('La cédula debe tener mínimo 7 dígitos numéricos.');
                    }
                    if (!ctype_digit($cedulaLimpia)) {
                        $fail('La cédula solo debe contener números.');
                    }
                },
            ],
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'email' => 'nullable|email|max:100',
            'telefono' => 'nullable|string|max:20',
            'genero' => 'nullable|in:M,F,Otro',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'institucion' => 'nullable|string|max:100',
            'tipo_visitante_id' => 'required|exists:tipos_visitante,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $visitante = Visitante::create($request->all());
        return response()->json($visitante, 201);
    }

    public function update(Request $request, $id)
    {
        $visitante = Visitante::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'cedula' => [
                'required',
                'unique:visitantes,cedula,' . $visitante->id,
                function ($attribute, $value, $fail) {
                    $cedulaLimpia = preg_replace('/[^0-9]/', '', $value);
                    if (strlen($cedulaLimpia) < 7) {
                        $fail('La cédula debe tener mínimo 7 dígitos numéricos.');
                    }
                    if (!ctype_digit($cedulaLimpia)) {
                        $fail('La cédula solo debe contener números.');
                    }
                },
            ],
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'email' => 'nullable|email|max:100',
            'telefono' => 'nullable|string|max:20',
            'genero' => 'nullable|in:M,F,Otro',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'institucion' => 'nullable|string|max:100',
            'tipo_visitante_id' => 'required|exists:tipos_visitante,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $visitante->update($request->all());
        return response()->json($visitante);
    }

    public function destroy($id)
    {
        try {
            $visitante = Visitante::findOrFail($id);
            $tieneVisitas = $visitante->visitas()->exists();

            if ($tieneVisitas) {
                $visitante->visitas()->delete();
                $visitante->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'Visitante y sus visitas eliminados correctamente'
                ]);
            } else {
                $visitante->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'Visitante eliminado correctamente'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el visitante: ' . $e->getMessage()
            ], 500);
        }
    }
}
