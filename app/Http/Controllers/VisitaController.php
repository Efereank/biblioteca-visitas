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
use App\Services\RegistroVisitaService;
use App\Services\CierreVisitaService;
use App\Services\MenorService;

class VisitaController extends Controller
{
    protected $registroVisitaService;
    protected $cierreVisitaService;
    protected $menorService;

    public function __construct(
        RegistroVisitaService $registroVisitaService,
        CierreVisitaService $cierreVisitaService,
        MenorService $menorService
    ) {
        $this->registroVisitaService = $registroVisitaService;
        $this->cierreVisitaService = $cierreVisitaService;
        $this->menorService = $menorService;
    }

    public function create(Request $request)
    {
        $tiposVisitante = \App\Models\TipoVisitante::all();
        $propositos = PropositoVisita::all();
        $actividades = Actividad::all();

        $user = Auth::user();
        if ($user && method_exists($user, 'isBibliotecario') && $user->isBibliotecario()) {
            $salas = $user->salas;
        } else {
            $salas = Sala::all();
        }

        $perfiles = PerfilInteres::with('subcategorias')->get();

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

    public function store(Request $request)
    {
        $user = Auth::user();
        $isBibliotecario = $user && method_exists($user, 'isBibliotecario') && $user->isBibliotecario();

        // Asignar sala por defecto al bibliotecario
        if ($isBibliotecario && empty($request->sala_id)) {
            $salas = $user->salas;
            if ($salas->isNotEmpty()) {
                $request->merge(['sala_id' => $salas->first()->id]);
            }
        }

        // Validación básica de la solicitud
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

        // Validación de cédula duplicada para visitantes nuevos
        if ($request->has('visitante_nuevo')) {
            $datosVisitante = $request->visitante_nuevo;
            if (!empty($datosVisitante['cedula'])) {
                $existente = Visitante::where('cedula', $datosVisitante['cedula'])->first();
                if ($existente) {
                    return response()->json([
                        'errors' => ['cedula' => ['Esta cédula ya está registrada']]
                    ], 422);
                }
            }
        }

        // Delegar toda la lógica de registro al Service
        $resultado = $this->registroVisitaService->registrar($request->all(), $user);

        // Construir mensaje de respuesta
        $mensaje = 'Visita registrada exitosamente';
        if ($resultado['menores_registrados'] > 0) {
            $mensaje .= '. Se registraron automáticamente ' . $resultado['menores_registrados'] . ' menor(es) a cargo.';
        }

        return response()->json([
            'message' => $mensaje,
            'visita' => $resultado['visita']->load(['visitante', 'sala'])
        ], 201);
    }

    public function registrarSalida($id)
    {
        $user = Auth::user();

        // Delegar toda la lógica de cierre al Service
        $resultado = $this->cierreVisitaService->registrarSalida($id, $user);

        if (!$resultado['success']) {
            return redirect()->route('visitas.historial')
                ->with('error', $resultado['mensaje']);
        }

        return redirect()->route('visitas.historial')
            ->with('success', $resultado['mensaje']);
    }

    public function historial(Request $request)
    {
        $user = Auth::user();
        $query = Visita::with(['visitante', 'proposito', 'sala'])
            ->orderBy('fecha_hora_entrada', 'desc');

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
