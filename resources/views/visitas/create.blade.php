@extends('layouts.app')

@section('content')
<div x-data="wizard()" x-init="init({{ json_encode($tiposVisitante) }}, {{ json_encode($propositos) }}, {{ json_encode($actividades) }}, {{ json_encode($visitantePrecargado) }})" class="max-w-4xl mx-auto">

    <div class="mb-6 md:mb-8">
        <div class="flex justify-between items-center">
            <template x-for="(step, index) in steps" :key="index">
                <div class="flex flex-col items-center w-1/4">
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
                 :style="`width: ${(currentStep / (steps.length - 1)) * 100}%`"></div>
        </div>
    </div>

    {{-- Formulario --}}
    <form @submit.prevent="submitForm" class="bg-white rounded-xl shadow-lg overflow-hidden">
        {{-- Paso 1: Identificar --}}
        <div x-show="currentStep === 0" class="p-4 md:p-6 lg:p-8">
            <h2 class="text-xl md:text-2xl font-bold text-gray-800 mb-4 md:mb-6">Identificar Visitante</h2>
            <div class="max-w-md">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Cédula de Identidad <span class="text-red-500">*</span>
                </label>
                <div class="flex rounded-md shadow-sm">
                    {{-- Select de prefijo (Nacionalidad) --}}
                    <select x-model="form.prefijo"
                            class="w-20 md:w-24 px-2 py-2 text-sm md:text-base rounded-l-md border border-r-0 border-gray-300 bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                        <option value="V">V</option>
                        <option value="E">E</option>
                    </select>

                    {{-- Input numérico de cédula --}}
                    <input type="text"
                           x-model="form.numeroCedula"
                           @input="formatearCedula"
                           @keyup.debounce.500="buscarCedula"
                           maxlength="8"
                           pattern="[0-9]*"
                           inputmode="numeric"
                           class="flex-1 min-w-0 block w-full px-3 md:px-4 py-2 text-sm md:text-base border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="12345678">

                    {{-- Botón buscar --}}
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
                <p class="text-xs text-gray-500 mt-1">
                    Seleccione el prefijo e ingrese su cédula
                </p>
                <p x-show="mensajeCedula" x-text="mensajeCedula" class="text-sm mt-2"
                   :class="{'text-green-600': visitanteEncontrado, 'text-yellow-600': !visitanteEncontrado && !errorCedula, 'text-red-600': errorCedula}"></p>
            </div>

            {{-- Visitante encontrado (libre) --}}
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

{{-- Visitante no encontrado (cédula disponible) --}}
<div x-show="!visitanteEncontrado && form.numeroCedula.length >= 7 && !errorCedula && cedulaVerificada && cedulaDisponible" class="mt-6" x-transition>
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <p class="text-sm text-blue-800 mb-2">Esta cédula no está registrada. Puede registrar un nuevo visitante.</p>
        <button type="button" @click="irAPasoDatos"
                class="bg-blue-600 text-white px-5 md:px-6 py-2 rounded-md hover:bg-blue-700 transition text-sm md:text-base">
            Registrar nuevo visitante
        </button>
    </div>
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

            {{-- Mensaje de cédula ya registrada pero sin visita activa --}}
            <div x-show="errorCedula && errorCedula.includes('ya está registrada') && !errorCedula.includes('visita activa')" class="mt-6 bg-red-50 border border-red-200 rounded-lg p-4" x-transition>
                <p class="text-sm text-red-800">
                    <span x-text="errorCedula"></span> Por favor, use el botón "Buscar" para continuar con el visitante existente.
                </p>
            </div>
        </div>

        {{-- Paso 2: Datos personales --}}
        <div x-show="currentStep === 1" class="p-4 md:p-6 lg:p-8">
            <h2 class="text-xl md:text-2xl font-bold text-gray-800 mb-4 md:mb-6">Datos del Visitante</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-6">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Cédula de Identidad <span class="text-red-500">*</span>
                    </label>
                    <div class="flex rounded-md shadow-sm max-w-md">
                        <select x-model="form.prefijo"
                                disabled
                                class="w-20 md:w-24 px-2 py-2 text-sm md:text-base rounded-l-md border border-r-0 border-gray-300 bg-gray-100">
                            <option value="V">V</option>
                            <option value="E">E</option>
                        </select>
                        <input type="text"
                               x-model="form.numeroCedula"
                               readonly
                               class="flex-1 block w-full px-3 md:px-4 py-2 text-sm md:text-base rounded-r-md border-gray-300 bg-gray-100"
                               placeholder="12345678">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">La cédula no se puede modificar</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Nombres <span class="text-red-500">*</span>
                    </label>
                    <input type="text" x-model="form.nombres" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm md:text-base">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Apellidos <span class="text-red-500">*</span>
                    </label>
                    <input type="text" x-model="form.apellidos" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm md:text-base">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" x-model="form.email"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm md:text-base">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                    <input type="text" x-model="form.telefono"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm md:text-base">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Género</label>
                    <select x-model="form.genero" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm md:text-base">
                        <option value="">Seleccione</option>
                        <option value="M">Masculino</option>
                        <option value="F">Femenino</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Fecha Nacimiento</label>
                    <input type="date" x-model="form.fecha_nacimiento"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm md:text-base">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Institución</label>
                    <input type="text" x-model="form.institucion"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm md:text-base">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">
                        Tipo de Visitante <span class="text-red-500">*</span>
                    </label>
                    <select x-model="form.tipo_visitante_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm md:text-base">
                        <option value="">Seleccione tipo</option>
                        <template x-for="tipo in tiposVisitante" :key="tipo.id">
                            <option :value="tipo.id" x-text="tipo.nombre"></option>
                        </template>
                    </select>
                </div>
            </div>
        </div>

        {{-- Paso 3: Datos de la visita --}}
        <div x-show="currentStep === 2" class="p-4 md:p-6 lg:p-8">
            <h2 class="text-xl md:text-2xl font-bold text-gray-800 mb-4 md:mb-6">Detalles de la Visita</h2>
            <div class="space-y-5 md:space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Propósito <span class="text-red-500">*</span>
                    </label>
                    <select x-model="form.proposito_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm md:text-base">
                        <option value="">Seleccione propósito</option>
                        <template x-for="p in propositos" :key="p.id">
                            <option :value="p.id" x-text="p.nombre"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Actividades realizadas</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 md:gap-3">
                        <template x-for="act in actividades" :key="act.id">
                            <label class="inline-flex items-center p-2 md:p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                                <input type="checkbox" :value="act.id" x-model="form.actividades_ids" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700" x-text="act.nombre"></span>
                            </label>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Observaciones</label>
                    <textarea x-model="form.observaciones" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm md:text-base"></textarea>
                </div>
            </div>
        </div>

        {{-- Paso 4: Confirmación --}}
        <div x-show="currentStep === 3" class="p-4 md:p-6 lg:p-8">
            <h2 class="text-xl md:text-2xl font-bold text-gray-800 mb-4 md:mb-6">Confirmar Registro</h2>
            <div class="bg-gray-50 rounded-lg p-4 md:p-6 space-y-4">
                <div class="border-b pb-4">
                    <h3 class="font-semibold text-gray-700">Visitante</h3>
                    <p class="text-base md:text-lg"><span x-text="form.nombres + ' ' + form.apellidos"></span></p>
                    <p class="text-sm text-gray-600">Cédula: <span x-text="cedulaCompleta"></span></p>
                    <p class="text-sm text-gray-600">Tipo: <span x-text="getTipoNombre(form.tipo_visitante_id)"></span></p>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-700">Visita</h3>
                    <p class="text-sm">Propósito: <span x-text="getPropositoNombre(form.proposito_id)"></span></p>
                    <p class="text-sm">Actividades: <span x-text="getActividadesNombres(form.actividades_ids) || 'Ninguna'"></span></p>
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
            <button type="button" x-show="currentStep > 0 && currentStep < 3" @click="siguiente"
                    class="w-full sm:w-auto px-4 md:px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Siguiente
            </button>
            <button type="submit" x-show="currentStep === 3"
                    class="w-full sm:w-auto px-4 md:px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                Confirmar Registro
            </button>
        </div>
    </form>
</div>


@endsection
