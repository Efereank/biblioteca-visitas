<?php

namespace App\Http\Controllers;

use App\Models\Visita;
use App\Models\Visitante;
use App\Models\PropositoVisita;
use App\Models\Actividad;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class VisitaController extends Controller
{

public function create(Request $request)
{
    $tiposVisitante = \App\Models\TipoVisitante::all();
    $propositos = PropositoVisita::all();
    $actividades = Actividad::all();

    $cedulaPrecargada = $request->query('cedula');
    $visitantePrecargado = null;

    if ($cedulaPrecargada) {
        $visitantePrecargado = Visitante::with('tipoVisitante')
            ->where('cedula', $cedulaPrecargada)
            ->first();

        if ($visitantePrecargado) {
            $visitaActiva = Visita::where('visitante_id', $visitantePrecargado->id)
                ->whereNull('fecha_hora_salida')
                ->first();

            if ($visitaActiva) {
                return redirect()->route('visitas.historial')
                    ->with('error', 'El visitante ya tiene una visita activa. Debe registrar la salida antes de crear una nueva visita.');
            }
        }
    }

    return view('visitas.create', compact(
        'tiposVisitante',
        'propositos',
        'actividades',
        'cedulaPrecargada',
        'visitantePrecargado'
    ));
}

    // Procesa el registro completo de visita
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'visitante_id' => 'required_without:visitante_nuevo|exists:visitantes,id',
            'visitante_nuevo' => 'required_without:visitante_id|array',
            'proposito_id' => 'required|exists:propositos_visita,id',
            'observaciones' => 'nullable|string',
            'actividades_ids' => 'nullable|array',
            'actividades_ids.*' => 'exists:actividades,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('visitante_nuevo')) {
            $visitanteData = $request->visitante_nuevo;

            $cedulaExistente = Visitante::where('cedula', $visitanteData['cedula'])->first();
            if ($cedulaExistente) {
                return response()->json([
                    'errors' => ['cedula' => ['Esta cédula ya está registrada']]
                ], 422);
            }

            $visitante = Visitante::create($visitanteData);
            $visitanteId = $visitante->id;
        } else {
            $visitanteId = $request->visitante_id;

            $visitaActiva = Visita::where('visitante_id', $visitanteId)
                ->whereNull('fecha_hora_salida')
                ->first();

            if ($visitaActiva) {
                return response()->json([
                    'errors' => ['visita_activa' => ['El visitante ya tiene una visita activa. Debe registrar la salida antes de crear una nueva visita.']]
                ], 422);
            }
        }

        $visita = Visita::create([
            'visitante_id' => $visitanteId,
            'proposito_id' => $request->proposito_id,
            'fecha_hora_entrada' => Carbon::now('America/Caracas'),
            'observaciones' => $request->observaciones,
            'actividades_ids' => $request->actividades_ids,
        ]);

        return response()->json([
            'message' => 'Visita registrada exitosamente',
            'visita' => $visita->load('visitante')
        ], 201);
    }

    // Registrar salida
    public function registrarSalida($id)
    {
        $visita = Visita::findOrFail($id);

        if (!$visita->fecha_hora_salida) {
            $visita->fecha_hora_salida = Carbon::now('America/Caracas');
            $visita->save();
            return redirect()->route('visitas.historial')
                ->with('success', 'Salida registrada correctamente');
        }

        return redirect()->route('visitas.historial')
            ->with('error', 'La visita ya tiene salida registrada');
    }

    // Historial con filtros
public function historial(Request $request)
{
    $query = Visita::with(['visitante', 'proposito'])
        ->orderBy('fecha_hora_entrada', 'desc');

    // Filtro por cédula
    if ($request->filled('cedula')) {
        $cedula = $request->cedula;
        $query->whereHas('visitante', function($q) use ($cedula) {
            $q->where('cedula', 'like', "%$cedula%");
        });
    }

    if ($request->filled('fecha_inicio')) {
        $query->whereDate('fecha_hora_entrada', '>=', $request->fecha_inicio);
    }

    if ($request->filled('fecha_fin')) {
        $query->whereDate('fecha_hora_entrada', '<=', $request->fecha_fin);
    }

    if ($request->filled('proposito')) {
        $query->where('proposito_id', $request->proposito);
    }

    if ($request->filled('estado')) {
        if ($request->estado == 'activo') {
            $query->whereNull('fecha_hora_salida');
        } elseif ($request->estado == 'finalizado') {
            $query->whereNotNull('fecha_hora_salida');
        }
    }

    $visitas = $query->paginate(15)->withQueryString();
    $propositos = PropositoVisita::select('id', 'nombre')->get();

    return view('visitas.historial', compact('visitas', 'propositos'));
}

public function verificarVisitaActiva($visitanteId)
{
    $visitaActiva = Visita::where('visitante_id', $visitanteId)
        ->whereNull('fecha_hora_salida')
        ->first();

    return response()->json([
        'tieneVisitaActiva' => !is_null($visitaActiva),
        'visita' => $visitaActiva
    ]);
}

    public function index() {
        return redirect()->route('visitas.historial');
    }

    public function show($id) {
        abort(404);
    }

    public function edit($id) {
        abort(404);
    }

    public function update(Request $request, $id) {
        abort(404);
    }

    public function destroy($id) {
        abort(404);
    }
}
