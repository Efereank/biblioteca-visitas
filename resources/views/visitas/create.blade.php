@extends('layouts.app')

@section('content')
<style>
    .wizard-content { visibility: hidden; }
    .wizard-ready .wizard-content { visibility: visible; }
</style>

<div x-data="wizard()"
     x-init="
     storeUrl = '{{ route('visitas.store') }}';
     historialUrl = '{{ route('visitas.historial') }}';
     role = '{{ $role }}';
     init({{ json_encode($tiposVisitante) }}, {{ json_encode($propositos) }}, {{ json_encode($actividades) }}, {{ json_encode($salas) }}, {{ json_encode($visitantePrecargado) }}, {{ json_encode($perfiles) }});
     perfilesInteres = {{ json_encode($perfiles->pluck('nombre')->toArray()) }};
     subcategorias = {{ json_encode($perfiles->mapWithKeys(function($p) { return [$p->nombre => $p->subcategorias->pluck('nombre')->toArray()]; })->toArray()) }};
     municipios = {{ json_encode(\App\Models\Municipio::all()) }};
     "
     :class="{ 'wizard-ready': inicializado }"
     class="max-w-4xl mx-auto">

    {{-- Indicador de progreso --}}
    <div class="wizard-content mb-6 md:mb-8">
        <div class="flex justify-between items-center">
            <template x-for="(step, index) in stepsVisibles" :key="index">
                <div class="flex flex-col items-center flex-1">
                    <div class="w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center text-xs md:text-sm font-medium transition-colors duration-300"
                         :class="{
                            'bg-blue-600 text-white': currentStep >= index,
                            'bg-gray-200 text-gray-600': currentStep < index
                         }">
                        <span x-text="index + 1"></span>
                    </div>
                    <span class="hidden sm:block text-xs mt-2 text-center" x-text="step.title"></span>
                    <span class="sm:hidden text-[10px] mt-1 text-center leading-tight" x-text="step.mobileTitle"></span>
                </div>
            </template>
        </div>
        <div class="relative mt-4">
            <div class="absolute top-0 h-1.5 bg-gray-200 w-full rounded-full"></div>
            <div class="absolute top-0 h-1.5 bg-blue-600 rounded-full transition-all duration-500"
                 :style="`width: ${(currentStep / (stepsVisibles.length - 1)) * 100}%`"></div>
        </div>
    </div>

    {{-- Indicador de carga --}}
    <div x-show="!inicializado" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <span class="ml-3 text-gray-500">Cargando...</span>
    </div>

    {{-- Formulario --}}
    <div class="wizard-content">
    <form @submit.prevent="submitForm" novalidate class="bg-white rounded-xl shadow-lg overflow-hidden">
        {{-- Paso 1: Identificar --}}
        <div x-show="currentStep === 0" class="p-4 md:p-6 lg:p-8">
            <h2 class="text-xl md:text-2xl font-bold text-gray-800 mb-4 md:mb-6">Identificar Visitante</h2>

            {{-- Selector de tipo de documento --}}
            <div class="max-w-md mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de documento <span class="text-red-500">*</span></label>
                <select x-model="form.tipo_documento" @change="onTipoDocumentoChange" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    <option value="C.I.">C.I. (Cédula de Identidad)</option>
                    <option value="Sin Identificación">Sin Identificación (Menor de edad)</option>
                </select>
            </div>

            {{-- Campo de cédula (visible para documentos que la requieran) --}}
            <div x-show="form.tipo_documento !== 'Sin Identificación'" class="max-w-md">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Número de documento <span class="text-red-500">*</span>
                </label>
                <div class="flex rounded-md shadow-sm">
                    <select x-model="form.prefijo" x-show="form.tipo_documento === 'C.I.'"
                            class="w-20 md:w-24 px-2 py-2 text-sm md:text-base rounded-l-md border border-r-0 border-gray-300 bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                        <option value="V">V</option>
                        <option value="E">E</option>
                    </select>
                    <input type="text"
                           x-model="form.numeroCedula"
                           @input="formatearCedula"
                           @keyup.debounce.500="buscarCedula"
                           maxlength="8"
                           pattern="[0-9]*"
                           inputmode="numeric"
                           class="flex-1 min-w-0 block w-full px-3 md:px-4 py-2 text-sm md:text-base border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                           :class="form.tipo_documento === 'C.I.' ? '' : 'rounded-l-md'"
                           placeholder="12345678">
                    <button type="button" @click="buscarCedula"
                            class="inline-flex items-center px-3 md:px-4 py-2 border border-l-0 border-gray-300 rounded-r-md bg-gray-50 text-gray-700 hover:bg-gray-100 text-sm md:text-base">
                        <span x-show="!verificandoVisitaActiva">Buscar</span>
                        <span x-show="verificandoVisitaActiva" class="flex items-center">
                            <svg class="animate-spin h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Verificando
                        </span>
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-1">Ingrese el número según el tipo de documento</p>
                <p x-show="mensajeCedula" x-text="mensajeCedula" class="text-sm mt-2"
                   :class="{'text-green-600': visitanteEncontrado, 'text-yellow-600': !visitanteEncontrado && !errorCedula, 'text-red-600': errorCedula}"></p>
            </div>

            {{-- Sección para menor sin identificación --}}
            <div x-show="form.tipo_documento === 'Sin Identificación'" class="mt-6 bg-gray-50 border border-gray-300 rounded-lg p-4" x-transition>
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Datos del Representante</h3>
                <p class="text-sm text-gray-600 mb-4">Seleccione el parentesco y complete los datos del adulto responsable.</p>

                {{-- Selector de parentesco --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Parentesco del representante <span class="text-red-500">*</span></label>
                    <select x-model="form.representante_parentesco" @change="onParentescoChange" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <option value="">Seleccione</option>
                        <option value="Padre">Padre</option>
                        <option value="Madre">Madre</option>
                        <option value="Tutor">Tutor</option>
                        <option value="Docente">Docente</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>

                {{-- Búsqueda de docente (visible solo si parentesco es Docente) --}}
                <div x-show="form.representante_parentesco === 'Docente'" class="mb-4 p-3 bg-white rounded border border-gray-200">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Buscar docente por cédula <span class="text-red-500">*</span></label>
                    <div class="flex rounded-md shadow-sm">
                        <input type="text" x-model="cedulaDocente" @keyup.enter="buscarDocente"
                               placeholder="Cédula del docente"
                               class="flex-1 block w-full rounded-l-md border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <button type="button" @click="buscarDocente"
                                class="px-4 py-2 bg-blue-600 text-white rounded-r-md hover:bg-blue-700 text-sm">
                            Buscar
                        </button>
                    </div>
                    <p x-show="buscandoDocente" class="text-gray-500 text-sm mt-1">Buscando docente...</p>
                    <p x-show="docenteEncontrado" class="text-green-600 text-sm mt-1">
                        ✓ Docente: <span x-text="docente?.nombres + ' ' + docente?.apellidos"></span>
                    </p>
                    <p x-show="errorDocente" x-text="errorDocente" class="text-red-600 text-sm mt-1"></p>
                </div>

{{-- Campos del representante (visibles si NO es Docente, o si es Docente pero no se encontró) --}}
<div x-show="form.representante_parentesco && form.representante_parentesco !== 'Docente' || (form.representante_parentesco === 'Docente' && !docenteEncontrado)">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Cédula del representante <span class="text-red-500">*</span></label>
            <div class="flex rounded-md shadow-sm mt-1">
                <input type="text" x-model="cedulaRepresentante" @keyup.enter="buscarRepresentante"
                       placeholder="Buscar cédula"
                       class="flex-1 block w-full rounded-l-md border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-sm">
                <button type="button" @click="buscarRepresentante"
                        class="px-4 py-2 bg-blue-600 text-white rounded-r-md hover:bg-blue-700 text-sm">
                    Buscar
                </button>
            </div>
            <p x-show="buscandoRepresentante" class="text-gray-500 text-sm mt-1">Buscando...</p>
            <p x-show="mensajeRepresentante" x-text="mensajeRepresentante" class="text-sm mt-1"
            :class="{
                'text-green-600': representanteEncontrado && representanteTieneVisitaActiva,
                'text-yellow-600': representanteEncontrado && !representanteTieneVisitaActiva,
                'text-gray-600': !representanteEncontrado
            }"></p>
            <p x-show="errorRepresentante" x-text="errorRepresentante" class="text-red-600 text-sm mt-1"></p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Nombre del representante <span class="text-red-500">*</span></label>
            <input type="text" x-model="form.representante_nombre" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                   :readonly="representanteEncontrado">
        </div>
    </div>
</div>

                <button type="button" @click="registrarMenor" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition text-sm">
                    Continuar como menor sin identificación
                </button>
                <p x-show="errorMenor" x-text="errorMenor" class="text-red-600 text-sm mt-2"></p>
            </div>

            {{-- Visitante encontrado --}}
            <div x-show="visitanteEncontrado && !errorCedula" class="mt-6 bg-green-50 border border-green-200 rounded-lg p-4" x-transition>
                <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                    <div class="flex-1">
                        <p class="font-medium text-gray-800">
                            <span x-text="visitante?.nombres + ' ' + visitante?.apellidos"></span>
                        </p>
                        <p class="text-sm text-gray-600">Tipo: <span x-text="visitante?.tipo_visitante?.nombre || 'No definido'"></span></p>
                        <p class="text-sm text-gray-600">Visitas previas: <span x-text="visitante?.visitas_count"></span></p>
                        <p class="text-sm text-gray-600">Cédula: <span x-text="visitante?.cedula"></span></p>
                    </div>
                    <button type="button" @click="siguienteConVisitante"
                            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition text-sm whitespace-nowrap">
                        Continuar con este visitante
                    </button>
                </div>
            </div>

            {{-- Cédula disponible (admin y recepcionista) --}}
            <div x-show="!visitanteEncontrado && form.numeroCedula.length >= 7 && !errorCedula && cedulaVerificada && cedulaDisponible && role !== 'bibliotecario' && form.tipo_documento !== 'Sin Identificación'" class="mt-6" x-transition>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-800 mb-2">Esta cédula no está registrada. Puede registrar un nuevo visitante.</p>
                    <button type="button" @click="irAPasoDatos"
                            class="bg-blue-600 text-white px-5 md:px-6 py-2 rounded-md hover:bg-blue-700 transition text-sm md:text-base">
                        Registrar nuevo visitante
                    </button>
                </div>
            </div>

            {{-- Aviso para bibliotecario --}}
            <div x-show="!visitanteEncontrado && form.numeroCedula.length >= 7 && !errorCedula && cedulaVerificada && cedulaDisponible && role === 'bibliotecario' && form.tipo_documento !== 'Sin Identificación'" class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4" x-transition>
                <p class="text-sm text-yellow-800">Este visitante no está registrado. Solicite al recepcionista que lo registre primero.</p>
            </div>

            {{-- Mensaje de visita activa --}}
            <div x-show="errorCedula && errorCedula.includes('visita activa')" class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4" x-transition>
                <div class="flex items-start gap-3">
                    <svg class="h-5 w-5 text-yellow-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm text-yellow-800 font-medium">Este visitante ya tiene una visita activa</p>
                        <p class="text-xs text-yellow-700 mt-1">Debe registrar la salida antes de crear una nueva visita.</p>
                        <a href="{{ route('visitas.historial') }}?estado=activo"
                           class="mt-3 inline-flex items-center text-sm text-yellow-800 hover:text-yellow-900 font-medium">
                            Ir al historial de visitas activas
                            <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Paso 2: Datos personales (admin y recepcionista) --}}
        <div x-show="role !== 'bibliotecario' && currentStep === 1" class="p-4 md:p-6 lg:p-8">
            <h2 class="text-xl md:text-2xl font-bold text-gray-800 mb-4 md:mb-6">Datos del Visitante</h2>

            {{-- Identificación --}}
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Identificación</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tipo de documento <span class="text-red-500">*</span></label>
                        <select x-model="form.tipo_documento" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="C.I.">C.I.</option>
                            <option value="Pasaporte">Pasaporte</option>
                            <option value="Partida de Nacimiento">Partida de Nacimiento</option>
                            <option value="Sin Identificación">Sin Identificación</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Número de documento <span class="text-red-500">*</span></label>
                        <div class="flex rounded-md shadow-sm mt-1">
                            <select x-model="form.prefijo" x-show="form.tipo_documento === 'C.I.'" disabled class="w-20 px-2 py-2 text-sm rounded-l-md border border-r-0 border-gray-300 bg-gray-100">
                                <option value="V">V</option>
                                <option value="E">E</option>
                            </select>
                            <input type="text" x-model="form.numeroCedula" readonly
                                   class="flex-1 block w-full px-3 py-2 text-sm rounded-r-md border-gray-300 bg-gray-100">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">El número de documento no se puede modificar</p>
                    </div>
                </div>
            </div>

            {{-- Datos básicos --}}
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Datos básicos</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombres <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.nombres" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Apellidos <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.apellidos" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha de nacimiento</label>
                        <input type="date" x-model="form.fecha_nacimiento" @change="calcularEdad" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Edad</label>
                        <input type="text" x-model="edad" readonly class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Sexo</label>
                        <select x-model="form.genero" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">Seleccione</option>
                            <option value="M">Masculino</option>
                            <option value="F">Femenino</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nacionalidad</label>
                        <input type="text" x-model="form.nacionalidad" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Teléfono móvil</label>
                        <input type="text" x-model="form.telefono" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Correo electrónico</label>
                        <input type="email" x-model="form.email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>
                </div>
            </div>

            {{-- Dirección --}}
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Dirección</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Dirección completa</label>
                        <textarea x-model="form.direccion" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"></textarea>
                    </div>

                    {{-- Municipio --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Municipio</label>
                    <select x-model="form.municipio" @change="cargarUbicacion(form.municipio)" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <option value="">Seleccione municipio</option>
                        <template x-for="m in municipios" :key="m.nombre">
                            <option :value="m.nombre" x-text="m.nombre"></option>
                        </template>
                    </select>
                    </div>

                    {{-- Parroquia --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Parroquia</label>
                        <select x-model="form.parroquia" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">Seleccione parroquia</option>
                            <template x-for="p in parroquias" :key="p.id">
                                <option :value="p.nombre" x-text="p.nombre"></option>
                            </template>
                        </select>
                    </div>

                    {{-- Ciudad --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Ciudad</label>
                        <select x-model="form.ciudad" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">Seleccione ciudad</option>
                            <template x-for="c in ciudades" :key="c.id">
                                <option :value="c.nombre" x-text="c.nombre"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Código postal</label>
                        <input type="text" x-model="form.codigo_postal" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>
                </div>
            </div>

            {{-- Educación y ocupación --}}
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Educación y ocupación</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Grado de instrucción</label>
                        <select x-model="form.grado_instruccion" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">Seleccione</option>
                            <option value="Sin estudios">Sin estudios</option>
                            <option value="Primaria">Primaria</option>
                            <option value="Secundaria">Secundaria</option>
                            <option value="Técnico">Técnico</option>
                            <option value="Universitario">Universitario</option>
                            <option value="Postgrado">Postgrado</option>
                        </select>
                    </div>
                    <div><label class="block text-sm font-medium text-gray-700">Profesión / oficio</label><input type="text" x-model="form.profesion" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"></div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Situación laboral</label>
                        <select x-model="form.situacion_laboral" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">Seleccione</option>
                            <option value="Estudiante">Estudiante</option>
                            <option value="Empleado">Empleado</option>
                            <option value="Jubilado">Jubilado</option>
                            <option value="Desempleado">Desempleado</option>
                            <option value="Independiente">Independiente</option>
                        </select>
                    </div>
                    <div><label class="block text-sm font-medium text-gray-700">Institución educativa o laboral</label><input type="text" x-model="form.institucion_educativa_laboral" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"></div>
                </div>
            </div>

            {{-- Necesidades especiales --}}
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Necesidades especiales</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">¿Tiene discapacidad?</label>
                        <select x-model="form.discapacidad" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="Ninguna">Ninguna</option>
                            <option value="Visual">Visual</option>
                            <option value="Motora">Motora</option>
                            <option value="Auditiva">Auditiva</option>
                            <option value="Cognitiva">Cognitiva</option>
                            <option value="Múltiple">Múltiple</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Necesidades especiales de lectura</label>
                        <select x-model="form.necesidades_especiales" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">Ninguna</option>
                            <option value="Letra grande">Letra grande</option>
                            <option value="Braille">Braille</option>
                            <option value="Audiolibro">Audiolibro</option>
                            <option value="Software lector de pantalla">Software lector de pantalla</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Consentimiento para comunicación</label>
                        <div class="mt-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" x-model="form.consentimiento_comunicacion" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Acepto recibir información por email/WhatsApp</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>



            {{-- Tipo de Visitante --}}
            <div class="p-4 bg-gray-50 rounded-lg">
                <label class="block text-sm font-medium text-gray-700">Tipo de Visitante <span class="text-red-500">*</span></label>
                <select x-model="form.tipo_visitante_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    <option value="">Seleccione tipo</option>
                    <template x-for="tipo in tiposVisitante" :key="tipo.id">
                        <option :value="tipo.id" x-text="tipo.nombre"></option>
                    </template>
                </select>
            </div>
        </div>

        {{-- Paso Visita (admin y bibliotecario) --}}
        <div x-show="isPasoVisita" class="p-4 md:p-6 lg:p-8">
            <h2 class="text-xl md:text-2xl font-bold text-gray-800 mb-4 md:mb-6">Detalles de la Visita</h2>
            <div class="space-y-5 md:space-y-6">

                {{-- Área de interés (siempre visible) --}}
                <div class="p-4 bg-gray-50 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">Área de interés</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Perfil de interés</label>
                            <select x-model="form.perfil_interes" @change="form.subcategoria_interes = ''" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">Seleccione perfil</option>
                                <template x-for="perfil in perfilesInteres" :key="perfil">
                                    <option :value="perfil" x-text="perfil"></option>
                                </template>
                            </select>
                        </div>
                        <div x-show="form.perfil_interes">
                            <label class="block text-sm font-medium text-gray-700">Subcategoría</label>
                            <select x-model="form.subcategoria_interes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">Seleccione</option>
                                <template x-for="sub in (subcategorias[form.perfil_interes] || [])" :key="sub">
                                    <option :value="sub" x-text="sub"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Propósito <span class="text-red-500">*</span></label>
                    <select x-model="form.proposito_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <option value="">Seleccione propósito</option>
                        <template x-for="p in propositos" :key="p.id">
                            <option :value="p.id" x-text="p.nombre"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Observaciones</label>
                    <textarea x-model="form.observaciones" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm md:text-base"></textarea>
                </div>
            </div>
        </div>

        {{-- Paso Confirmación --}}
        <div x-show="currentStep === stepsVisibles.length - 1" class="p-4 md:p-6 lg:p-8">
            <h2 class="text-xl md:text-2xl font-bold text-gray-800 mb-4 md:mb-6">Confirmar Registro</h2>
            <div class="bg-gray-50 rounded-lg p-4 md:p-6 space-y-4">

            {{-- Propósito de visita (visible solo para recepcionista) --}}
            <div class="mb-6 p-4 bg-gray-50 rounded-lg" x-show="role === 'recepcionista'">
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Propósito de la visita</h3>
                <label class="block text-sm font-medium text-gray-700">Propósito <span class="text-red-500">*</span></label>
                <select x-model="form.proposito_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    <option value="">Seleccione propósito</option>
                    <template x-for="p in propositos" :key="p.id">
                        <option :value="p.id" x-text="p.nombre"></option>
                    </template>
                </select>
            </div>

                <div class="border-b pb-4">
                    <h3 class="font-semibold text-gray-700">Visitante</h3>
                    <p class="text-base md:text-lg"><span x-text="form.nombres + ' ' + form.apellidos"></span></p>
                    <p class="text-sm text-gray-600">Documento: <span x-text="form.tipo_documento + ' ' + cedulaCompleta"></span></p>
                    <p class="text-sm text-gray-600">Tipo: <span x-text="getTipoNombre(form.tipo_visitante_id)"></span></p>
                    <p class="text-sm text-gray-600" x-show="form.perfil_interes">Interés: <span x-text="form.perfil_interes"></span></p>
                    <div x-show="form.tipo_documento === 'Sin Identificación'" class="mt-2 text-sm text-gray-700 bg-gray-50 p-2 rounded border border-gray-200">
                        <p><strong>Representante:</strong> <span x-text="form.representante_nombre"></span></p>
                        <p><strong>Parentesco:</strong> <span x-text="form.representante_parentesco"></span></p>
                    </div>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-700">Visita</h3>
                    <p class="text-sm" x-show="role !== 'recepcionista' && form.sala_id">Sala: <span x-text="getSalaNombre(form.sala_id)"></span></p>
                    <p class="text-sm">Propósito: <span x-text="getPropositoNombre(form.proposito_id)"></span></p>
                    <p class="text-sm">Observaciones: <span x-text="form.observaciones || 'Ninguna'"></span></p>
                    <p class="text-sm mt-2">Fecha y hora: <span x-text="new Date().toLocaleString('es-VE')"></span></p>
                </div>
            </div>
        </div>

        {{-- Botones de navegación --}}
        <div class="bg-gray-50 px-4 md:px-6 py-3 md:py-4 flex flex-col sm:flex-row gap-3 sm:gap-0 justify-between border-t">
            <button type="button" x-show="currentStep > 0" @click="currentStep--"
                    class="w-full sm:w-auto px-4 md:px-6 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Anterior
            </button>
            <div class="flex-1"></div>
            <button type="button" x-show="currentStep > 0 && currentStep < stepsVisibles.length - 1" @click="siguiente"
                    class="w-full sm:w-auto px-4 md:px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Siguiente
            </button>
            <button type="submit" x-show="currentStep === stepsVisibles.length - 1"
                    class="w-full sm:w-auto px-4 md:px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                Confirmar Registro
            </button>
        </div>
    </form>
    </div>
</div>
@endsection
