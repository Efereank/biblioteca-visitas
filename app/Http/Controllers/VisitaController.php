<?php

namespace App\Http\Controllers;

use App\Models\Visita;
use App\Models\Visitante;
use App\Models\PropositoVisita;
use App\Models\Actividad;
use App\Models\Sala;
use App\Models\PerfilInteres;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class VisitaController extends Controller
{
    public function create(Request $request)
    {
        $tiposVisitante = \App\Models\TipoVisitante::all();
        $propositos = PropositoVisita::all();
        $actividades = Actividad::all();

        // Salas según rol
        $user = Auth::user();
        if ($user && method_exists($user, 'isBibliotecario') && $user->isBibliotecario()) {
            $salas = $user->salas;
        } else {
            $salas = Sala::all();
        }

        // Intereses para paso 3
        $perfiles = PerfilInteres::with('subcategorias')->get();

        // Precargar cédula
        $cedulaPrecargada = $request->query('cedula');
        $visitantePrecargado = null;

        if ($cedulaPrecargada) {
            $visitantePrecargado = Visitante::with('tipoVisitante')
                ->where('cedula', $cedulaPrecargada)
                ->first();

            if ($visitantePrecargado) {
                $userRole = Auth::user()->role;

                if ($userRole === 'recepcionista' || $userRole === 'admin') {
                    $visitaActiva = Visita::where('visitante_id', $visitantePrecargado->id)
                        ->whereNull('fecha_hora_salida')
                        ->first();

                    if ($visitaActiva) {
                        return redirect()->route('visitas.historial')
                            ->with('error', 'El visitante ya tiene una visita activa. Debe registrar la salida antes de crear una nueva visita.');
                    }
                }
            }
        }

        $role = Auth::user()->role;

        return view('visitas.create', compact(
            'tiposVisitante',
            'propositos',
            'actividades',
            'salas',
            'cedulaPrecargada',
            'visitantePrecargado',
            'perfiles',
            'role'
        ));
    }

    // Procesa el registro completo de visita
    public function store(Request $request)
    {
        $user = Auth::user();
        $isBibliotecario = $user && method_exists($user, 'isBibliotecario') && $user->isBibliotecario();

        // Si es bibliotecario y no envió sala, asignar la primera que tiene
        if ($isBibliotecario && empty($request->sala_id)) {
            $salas = $user->salas;
            if ($salas->isNotEmpty()) {
                $request->merge(['sala_id' => $salas->first()->id]);
            }
        }

        $validator = Validator::make($request->all(), [
            'visitante_id' => 'required_without:visitante_nuevo|exists:visitantes,id',
            'visitante_nuevo' => 'required_without:visitante_id|array',
            'proposito_id' => 'required|exists:propositos_visita,id',
            'sala_id' => 'nullable|exists:salas,id',
            'observaciones' => 'nullable|string',
            'actividades_ids' => 'nullable|array',
            'actividades_ids.*' => 'exists:actividades,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('visitante_nuevo')) {
            $visitanteData = $request->visitante_nuevo;

            // Validar cédula solo si se envió una (no es menor sin identificación)
            if (!empty($visitanteData['cedula'])) {
                $cedulaExistente = Visitante::where('cedula', $visitanteData['cedula'])->first();
                if ($cedulaExistente) {
                    return response()->json([
                        'errors' => ['cedula' => ['Esta cédula ya está registrada']]
                    ], 422);
                }
            } else {
                // Si no hay cédula, generar código temporal
                $visitanteData['cedula'] = null;
            }

            // Convertir docente_id vacío a null
            if (empty($visitanteData['docente_id'])) {
                $visitanteData['docente_id'] = null;
            }

            // Limpiar campos de representante si no aplican
            if ($visitanteData['tipo_documento'] !== 'Sin Identificación') {
                $visitanteData['representante_nombre'] = null;
                $visitanteData['representante_cedula'] = null;
                $visitanteData['representante_parentesco'] = null;
                $visitanteData['docente_id'] = null;
            }

            $visitanteData['fecha_registro'] = now();
            $visitanteData['usuario_registrador_id'] = $user->id;

            // Generar código temporal si la cédula es null
            if (empty($visitanteData['cedula'])) {
                $visitanteData['cedula'] = Visitante::generarCodigoTemporal(
                    $visitanteData['nombres'],
                    $visitanteData['apellidos']
                );
            }

            $visitante = Visitante::create($visitanteData);
            $visitanteId = $visitante->id;
        } else {
            $visitanteId = $request->visitante_id;

            if ($isBibliotecario) {
                // El bibliotecario NECESITA que exista una visita general activa (sin sala)
                $visitaGeneralActiva = Visita::where('visitante_id', $visitanteId)
                    ->whereNull('sala_id')
                    ->whereNull('fecha_hora_salida')
                    ->first();

                if (!$visitaGeneralActiva) {
                    return response()->json([
                        'errors' => ['visita_activa' => ['El visitante no ha registrado su entrada general. Debe pasar primero por recepción.']]
                    ], 422);
                }

                // Verificar que no tenga ya una visita activa en ESTA MISMA sala
                if ($request->filled('sala_id')) {
                    $visitaEnSala = Visita::where('visitante_id', $visitanteId)
                        ->whereNull('fecha_hora_salida')
                        ->where('sala_id', $request->sala_id)
                        ->first();

                    if ($visitaEnSala) {
                        return response()->json([
                            'errors' => ['visita_activa' => ['El visitante ya tiene una visita activa en esta sala.']]
                        ], 422);
                    }
                }
            } else {
                // Recepcionista o admin: bloquear con cualquier visita activa
                $visitaActiva = Visita::where('visitante_id', $visitanteId)
                    ->whereNull('fecha_hora_salida')
                    ->first();

                if ($visitaActiva) {
                    return response()->json([
                        'errors' => ['visita_activa' => ['El visitante ya tiene una visita activa. Debe registrar la salida antes de crear una nueva visita.']]
                    ], 422);
                }
            }

            // Actualizar intereses si se enviaron
            if ($request->has('visitante_intereses')) {
                $visitante = Visitante::find($visitanteId);
                $visitante->perfil_interes = $request->input('visitante_intereses.perfil_interes');
                $visitante->subcategoria_interes = $request->input('visitante_intereses.subcategoria_interes');
                $visitante->save();
            }
        }

        // --- Creación de la visita principal ---
        $visita = Visita::create([
            'visitante_id' => $visitanteId,
            'proposito_id' => $request->proposito_id,
            'sala_id' => $request->sala_id,
            'fecha_hora_entrada' => Carbon::now('America/Caracas'),
            'observaciones' => $request->observaciones,
            'actividades_ids' => $request->actividades_ids,
        ]);

        // --- REGISTRO AUTOMÁTICO DE MENORES A CARGO (solo bibliotecario en sala) ---
        if ($isBibliotecario && $request->filled('sala_id') && $visitanteId) {
            $representante = Visitante::find($visitanteId);
            if ($representante) {
                // Buscar menores por docente_id y por representante_cedula
                $menores = Visitante::where(function ($query) use ($representante) {
                        $query->where('docente_id', $representante->id);
                        if (!empty($representante->cedula)) {
                            $query->orWhere('representante_cedula', $representante->cedula);
                        }
                    })
                    ->get();

                $registrados = 0;
                foreach ($menores as $menor) {
                    // Verificar que el menor no tenga ya una visita activa en ESTA sala
                    $visitaMenorActiva = Visita::where('visitante_id', $menor->id)
                        ->where('sala_id', $request->sala_id)
                        ->whereNull('fecha_hora_salida')
                        ->first();

                    if (!$visitaMenorActiva) {
                        Visita::create([
                            'visitante_id' => $menor->id,
                            'proposito_id' => $request->proposito_id,
                            'sala_id' => $request->sala_id,
                            'fecha_hora_entrada' => Carbon::now('America/Caracas'),
                            'observaciones' => 'Registro automático por representante',
                            'actividades_ids' => $request->actividades_ids,
                        ]);
                        $registrados++;
                    }
                }

                if ($registrados > 0) {
                    return response()->json([
                        'message' => 'Visita registrada exitosamente. Se registraron automáticamente ' . $registrados . ' menor(es) a cargo.',
                        'visita' => $visita->load(['visitante', 'sala'])
                    ], 201);
                }
            }
        }

        return response()->json([
            'message' => 'Visita registrada exitosamente',
            'visita' => $visita->load(['visitante', 'sala'])
        ], 201);
    }

    // Registrar salida
    public function registrarSalida($id)
    {
        $user = Auth::user();
        $visita = Visita::with('visitante')->findOrFail($id);

        if ($visita->fecha_hora_salida) {
            return redirect()->route('visitas.historial')
                ->with('error', 'La visita ya tiene salida registrada');
        }

        $ahora = Carbon::now('America/Caracas');
        $visita->fecha_hora_salida = $ahora;
        $visita->save();

        $visitante = $visita->visitante;
        $menoresCerrados = 0;

        // Determinar si el usuario actual puede cerrar todas las visitas del visitante
        // (recepcionista o admin cierran todo; bibliotecario solo la visita concreta)
        $cerrarTodas = ($user->role === 'recepcionista' || $user->role === 'admin');

        if ($cerrarTodas && $visitante) {
            // Cerrar todas las visitas activas restantes del mismo visitante (sin contar la actual)
            Visita::where('visitante_id', $visitante->id)
                ->whereNull('fecha_hora_salida')
                ->where('id', '!=', $visita->id)
                ->update(['fecha_hora_salida' => $ahora]);
        }

        if ($visitante) {
            // 1. Menores asociados por docente_id
            $menoresPorDocente = Visita::whereHas('visitante', function ($q) use ($visitante) {
                    $q->where('docente_id', $visitante->id);
                })
                ->whereNull('fecha_hora_salida')
                ->get();

            foreach ($menoresPorDocente as $visitaMenor) {
                $visitaMenor->fecha_hora_salida = $ahora;
                $visitaMenor->save();
                $menoresCerrados++;
            }

            // 2. Menores asociados por cédula del representante
            if (!empty($visitante->cedula)) {
                $menoresPorCedula = Visita::whereHas('visitante', function ($q) use ($visitante) {
                        $q->where('representante_cedula', $visitante->cedula);
                    })
                    ->whereNull('fecha_hora_salida')
                    ->get();

                foreach ($menoresPorCedula as $visitaMenor) {
                    $visitaMenor->fecha_hora_salida = $ahora;
                    $visitaMenor->save();
                    $menoresCerrados++;
                }
            }
        }

        $mensaje = 'Salida registrada correctamente';
        if ($menoresCerrados > 0) {
            $mensaje .= ". También se registró la salida de {$menoresCerrados} menor(es) a cargo.";
        }
        if ($cerrarTodas && $visitante) {
            $mensaje .= " Se han cerrado todas las visitas activas del visitante.";
        }

        return redirect()->route('visitas.historial')
            ->with('success', $mensaje);
    }

    // Historial con filtros
public function historial(Request $request)
{
    $user = Auth::user();
    $query = Visita::with(['visitante', 'proposito', 'sala'])
        ->orderBy('fecha_hora_entrada', 'desc');

    // Si es bibliotecario, solo ve las visitas de sus salas
    if ($user && method_exists($user, 'isBibliotecario') && $user->isBibliotecario()) {
        $salasIds = $user->salas->pluck('id');
        $query->whereIn('sala_id', $salasIds);
    }

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

    if ($request->filled('sala')) {
        $query->where('sala_id', $request->sala);
    }

    if ($request->filled('estado')) {
        if ($request->estado == 'activo') {
            $query->whereNull('fecha_hora_salida');
        } elseif ($request->estado == 'finalizado') {
            $query->whereNotNull('fecha_hora_salida');
        }
    }

    $visitas = $query->paginate(10)->withQueryString();

    $propositos = PropositoVisita::select('id', 'nombre')->get();

    $salas = ($user && method_exists($user, 'isBibliotecario') && $user->isBibliotecario())
        ? $user->salas
        : Sala::select('id', 'nombre')->get();

    return view('visitas.historial', compact('visitas', 'propositos', 'salas'));
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

    public function index() { return redirect()->route('visitas.historial'); }
    public function show($id) { abort(404); }
    public function edit($id) { abort(404); }
    public function update(Request $request, $id) { abort(404); }
    public function destroy($id) { abort(404); }
}
