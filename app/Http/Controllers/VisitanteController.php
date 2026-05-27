<?php

namespace App\Http\Controllers;

use App\Models\Visitante;
use App\Models\TipoVisitante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\Visita;

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

    public function searchByCedula($cedula = null)
    {
        // Si viene vacío o es un código temporal, devolver no encontrado
        if (empty($cedula) || str_starts_with($cedula, 'TMP-')) {
            return response()->json(['message' => 'Visitante no encontrado'], 404);
        }

        $cedulaLimpia = preg_replace('/[^0-9]/', '', $cedula);

        if (empty($cedulaLimpia)) {
            return response()->json(['message' => 'Visitante no encontrado'], 404);
        }

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
        $visitante->append(['nombre_completo', 'edad', 'es_frecuente']);

        if (!isset($visitante->visitas_count)) {
            $visitante->loadCount('visitas');
        }

        return response()->json($visitante);
    }

    public function generarQR($id)
    {
        $visitante = Visitante::with('tipoVisitante')->findOrFail($id);
        $url = route('visitas.create', ['cedula' => $visitante->cedula]);

        $qr = QrCode::size(250)
            ->backgroundColor(255, 255, 255)
            ->color(0, 0, 0)
            ->margin(10)
            ->generate($url);

        return view('visitantes.qr', compact('visitante', 'qr'));
    }

    public function store(Request $request)
    {
        // Validación dinámica según tipo de documento
        $validator = Validator::make($request->all(), [
            'tipo_documento' => 'nullable|in:C.I.,Pasaporte,Partida de Nacimiento,Sin Identificación,Otro',
            'cedula' => [
                function ($attribute, $value, $fail) use ($request) {
                    $tipo = $request->tipo_documento;
                    if (in_array($tipo, ['C.I.', 'Pasaporte']) && empty($value)) {
                        $fail('El número de documento es obligatorio para ' . $tipo . '.');
                    }
                    if (!empty($value)) {
                        $cedulaLimpia = preg_replace('/[^0-9]/', '', $value);
                        if (strlen($cedulaLimpia) < 7) {
                            $fail('El número de documento debe tener mínimo 7 dígitos.');
                        }
                        $exists = Visitante::where('cedula', $cedulaLimpia)->exists();
                        if ($exists) {
                            $fail('Este número de documento ya está registrado.');
                        }
                    }
                },
            ],
            'representante_nombre' => 'required_if:tipo_documento,Sin Identificación|nullable|string|max:100',
            'representante_cedula' => 'required_if:tipo_documento,Sin Identificación|nullable|string|max:20',
            'representante_parentesco' => 'required_if:tipo_documento,Sin Identificación|nullable|in:Padre,Madre,Tutor,Docente,Otro|max:50',
            'docente_id' => 'nullable|exists:visitantes,id',
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'email' => 'nullable|email|max:100',
            'telefono' => 'nullable|string|max:20',
            'genero' => 'nullable|in:M,F,Otro',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'institucion' => 'nullable|string|max:100',
            'tipo_visitante_id' => 'required|exists:tipos_visitante,id',
            'nacionalidad' => 'nullable|string|max:50',
            'direccion' => 'nullable|string|max:255',
            'municipio' => 'nullable|string|max:100',
            'parroquia' => 'nullable|string|max:100',
            'ciudad' => 'nullable|string|max:100',
            'codigo_postal' => 'nullable|string|max:10',
            'grado_instruccion' => 'nullable|string|max:50',
            'profesion' => 'nullable|string|max:100',
            'situacion_laboral' => 'nullable|string|max:50',
            'institucion_educativa_laboral' => 'nullable|string|max:150',
            'perfil_interes' => 'nullable|string|max:100',
            'subcategoria_interes' => 'nullable|string|max:150',
            'formato_preferido' => 'nullable|string|max:50',
            'idiomas_interes' => 'nullable|array',
            'discapacidad' => 'nullable|string|max:50',
            'necesidades_especiales' => 'nullable|string|max:100',
            'consentimiento_comunicacion' => 'nullable|boolean',
            'observaciones' => 'nullable|string',
        ], [
            'nombres.required' => 'Los nombres son obligatorios.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'tipo_visitante_id.required' => 'El tipo de visitante es obligatorio.',
            'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'representante_nombre.required_if' => 'El nombre del representante es obligatorio para menores sin identificación.',
            'representante_cedula.required_if' => 'La cédula del representante es obligatoria.',
            'representante_parentesco.required_if' => 'El parentesco del representante es obligatorio.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();

        if (empty($data['cedula'])) {
            $data['cedula'] = Visitante::generarCodigoTemporal(
                $request->nombres,
                $request->apellidos
            );
        } else {
            // Limpiar cédula si es numérica
            if (in_array($request->tipo_documento, ['C.I.', 'Pasaporte'])) {
                $data['cedula'] = preg_replace('/[^0-9]/', '', $data['cedula']);
            }
        }

        $data['fecha_registro'] = now();
        $data['usuario_registrador_id'] = Auth::id();

        $visitante = Visitante::create($data);
        $visitante->load('tipoVisitante');

        return response()->json($visitante, 201);
    }

    public function update(Request $request, $id)
    {
        $visitante = Visitante::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'tipo_documento' => 'nullable|in:C.I.,Pasaporte,Partida de Nacimiento,Sin Identificación,Otro',
            'cedula' => [
                function ($attribute, $value, $fail) use ($request, $visitante) {
                    $tipo = $request->tipo_documento;
                    if (in_array($tipo, ['C.I.', 'Pasaporte']) && empty($value)) {
                        $fail('El número de documento es obligatorio para ' . $tipo . '.');
                    }
                    if (!empty($value)) {
                        $cedulaLimpia = preg_replace('/[^0-9]/', '', $value);
                        if (strlen($cedulaLimpia) < 7) {
                            $fail('El número de documento debe tener mínimo 7 dígitos.');
                        }
                        $exists = Visitante::where('cedula', $cedulaLimpia)
                            ->where('id', '!=', $visitante->id)
                            ->exists();
                        if ($exists) {
                            $fail('Este número de documento ya está registrado por otro visitante.');
                        }
                    }
                },
            ],
            'representante_nombre' => 'required_if:tipo_documento,Sin Identificación|nullable|string|max:100',
            'representante_cedula' => 'required_if:tipo_documento,Sin Identificación|nullable|string|max:20',
            'representante_parentesco' => 'required_if:tipo_documento,Sin Identificación|nullable|in:Padre,Madre,Tutor,Docente,Otro|max:50',
            'docente_id' => 'nullable|exists:visitantes,id',
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'email' => 'nullable|email|max:100',
            'telefono' => 'nullable|string|max:20',
            'genero' => 'nullable|in:M,F,Otro',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'institucion' => 'nullable|string|max:100',
            'tipo_visitante_id' => 'required|exists:tipos_visitante,id',
            'nacionalidad' => 'nullable|string|max:50',
            'direccion' => 'nullable|string|max:255',
            'municipio' => 'nullable|string|max:100',
            'parroquia' => 'nullable|string|max:100',
            'ciudad' => 'nullable|string|max:100',
            'codigo_postal' => 'nullable|string|max:10',
            'grado_instruccion' => 'nullable|string|max:50',
            'profesion' => 'nullable|string|max:100',
            'situacion_laboral' => 'nullable|string|max:50',
            'institucion_educativa_laboral' => 'nullable|string|max:150',
            'perfil_interes' => 'nullable|string|max:100',
            'subcategoria_interes' => 'nullable|string|max:150',
            'formato_preferido' => 'nullable|string|max:50',
            'idiomas_interes' => 'nullable|array',
            'discapacidad' => 'nullable|string|max:50',
            'necesidades_especiales' => 'nullable|string|max:100',
            'consentimiento_comunicacion' => 'nullable|boolean',
            'observaciones' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();

        // Si no se envió cédula, generar código temporal (solo si antes no tenía)
        if (empty($data['cedula']) && empty($visitante->cedula)) {
            $data['cedula'] = Visitante::generarCodigoTemporal(
                $request->nombres,
                $request->apellidos
            );
        } elseif (!empty($data['cedula'])) {
            if (in_array($request->tipo_documento, ['C.I.', 'Pasaporte'])) {
                $data['cedula'] = preg_replace('/[^0-9]/', '', $data['cedula']);
            }
        }

        $data['fecha_ultima_modificacion'] = now();

        $visitante->update($data);
        $visitante->refresh();
        $visitante->load('tipoVisitante');
        $visitante->loadCount('visitas');

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

public function menoresActivos($id)
{
    $visitante = Visitante::findOrFail($id);

    $menoresActivos = Visita::whereHas('visitante', function($q) use ($id) {
            $q->where('docente_id', $id);
        })
        ->whereNull('fecha_hora_salida')
        ->count();

    return response()->json([
        'menoresActivos' => $menoresActivos
    ]);
}
}
