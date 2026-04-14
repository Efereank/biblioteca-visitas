@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between">
    <form method="GET" class="w-full sm:max-w-md">
        <div class="relative">
            <input type="text" name="search" id="search" value="{{ request('search') }}"
                   placeholder="Buscar por cédula o nombre..."
                   class="block w-full rounded-lg border-gray-300 pl-4 pr-12 py-2 md:py-3 text-sm md:text-base shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <button type="submit" class="absolute inset-y-0 right-0 flex items-center pr-3">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </button>
        </div>
    </form>

    @if(request('search'))
    <a href="{{ route('visitantes.index') }}"
       class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
        Limpiar filtros
    </a>
    @endif
</div>

{{-- Contenedor principal con Alpine --}}
<div x-data="visitantesManager()">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
        @forelse($visitantes as $visitante)
        @php
            $visitaActiva = App\Models\Visita::where('visitante_id', $visitante->id)
                ->whereNull('fecha_hora_salida')
                ->first();
        @endphp
        <div class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:scale-[1.02] transition-all duration-200">
            <div class="h-2" style="background-color: {{ $visitante->tipoVisitante->color }}"></div>
            <div class="p-4 md:p-5">
                {{-- Etiqueta de visitante activo --}}
                @if($visitaActiva)
                <div class="mb-3">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-300">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-1.5 animate-pulse"></span>
                        Visita activa
                    </span>
                    <span class="ml-2 text-xs text-gray-500">
                        Desde: {{ \Carbon\Carbon::parse($visitaActiva->fecha_hora_entrada)->format('H:i') }}
                    </span>
                </div>
                @endif

                <div class="flex justify-between items-start">
                    <h3 class="text-base md:text-lg font-bold text-gray-800 truncate pr-2">{{ $visitante->nombre_completo }}</h3>
                    <div class="flex items-center gap-1 flex-shrink-0">
                        @if($visitante->es_frecuente)
                            <span class="text-yellow-500 text-lg md:text-xl" title="Visitante frecuente (5+ visitas)">★</span>
                        @endif
                        {{-- Botón eliminar --}}
                        <button @click="eliminarVisitante({{ $visitante->id }})"
                                class="text-red-500 hover:text-red-700 p-1 rounded-full hover:bg-red-50 transition"
                                title="Eliminar visitante">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <p class="text-xs md:text-sm text-gray-600 mt-1">Cédula: {{ $visitante->cedula }}</p>
                <p class="text-xs md:text-sm text-gray-600">Tipo: {{ $visitante->tipoVisitante->nombre }}</p>
                <p class="text-xs md:text-sm text-gray-600">Visitas: <span class="font-medium">{{ $visitante->visitas_count }}</span></p>

                <div class="mt-4 md:mt-5 flex flex-col sm:flex-row gap-2 sm:gap-0 sm:justify-between sm:items-center">
                    <button @click="verDetalle({{ $visitante->id }})"
                            class="text-blue-600 hover:text-blue-800 text-xs md:text-sm font-medium text-left">
                        Ver detalles
                    </button>

                    @if($visitaActiva)
                        <span class="bg-gray-100 text-gray-600 px-3 md:px-4 py-1.5 md:py-2 rounded-lg text-xs md:text-sm text-center cursor-not-allowed">
                            Visita en curso
                        </span>
                    @else
                        <a href="{{ route('visitas.create', ['cedula' => $visitante->cedula]) }}"
                           class="bg-blue-600 text-white px-3 md:px-4 py-1.5 md:py-2 rounded-lg text-xs md:text-sm hover:bg-blue-700 transition text-center">
                            Nueva visita
                        </a>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No hay visitantes</h3>
            <p class="mt-1 text-sm text-gray-500">
                @if(request('search'))
                    No se encontraron visitantes con "{{ request('search') }}"
                @else
                    Comienza registrando un nuevo visitante
                @endif
            </p>
        </div>
        @endforelse
    </div>

    {{-- Modal de detalles --}}
    <div x-show="modalAbierto"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;"
         @click.self="cerrarModal"
         @keydown.escape.window="cerrarModal">
        <div class="flex items-center justify-center min-h-screen px-4 py-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full">
                <button @click="cerrarModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 z-10">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>

                <div class="px-6 pt-6 pb-4" x-show="cargandoDetalle">
                    <div class="flex justify-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    </div>
                    <p class="text-center text-gray-500">Cargando detalles...</p>
                </div>

                <div x-show="!cargandoDetalle && visitanteSeleccionado">
                    <div class="px-6 pt-6 pb-4">
                        <div class="flex items-center justify-between mb-4 pr-8">
                            <h3 class="text-xl font-bold text-gray-900" x-text="visitanteSeleccionado.nombre_completo || ''"></h3>
                            {{-- Etiqueta de activo en el modal --}}
                            <span x-show="visitanteSeleccionado?.visita_activa"
                                  class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-300">
                                <span class="w-2 h-2 bg-green-500 rounded-full mr-1.5 animate-pulse"></span>
                                Visita activa
                            </span>
                        </div>
                        <div class="space-y-3 text-sm">
                            <div class="grid grid-cols-1 gap-2">
                                <p><span class="font-medium text-gray-700">Cédula:</span> <span x-text="visitanteSeleccionado.cedula || ''"></span></p>
                                <p><span class="font-medium text-gray-700">Email:</span> <span x-text="visitanteSeleccionado.email || 'No registrado'"></span></p>
                                <p><span class="font-medium text-gray-700">Teléfono:</span> <span x-text="visitanteSeleccionado.telefono || 'No registrado'"></span></p>
                                <p><span class="font-medium text-gray-700">Género:</span> <span x-text="visitanteSeleccionado.genero || 'No especificado'"></span></p>
                                <p><span class="font-medium text-gray-700">Fecha nacimiento:</span>
                                    <span x-text="visitanteSeleccionado.fecha_nacimiento ? new Date(visitanteSeleccionado.fecha_nacimiento).toLocaleDateString('es-ES') : 'No registrada'"></span>
                                </p>
                                <p><span class="font-medium text-gray-700">Institución:</span> <span x-text="visitanteSeleccionado.institucion || 'No registrada'"></span></p>
                                <p>
                                    <span class="font-medium text-gray-700">Tipo de visitante:</span>
                                    <span class="px-2 py-0.5 rounded text-white text-xs"
                                          :style="'background-color: ' + (visitanteSeleccionado.tipo_visitante?.color || '#6c757d')"
                                          x-text="visitanteSeleccionado.tipo_visitante?.nombre || ''"></span>
                                </p>
                                <p><span class="font-medium text-gray-700">Total visitas:</span> <span x-text="visitanteSeleccionado.visitas_count || 0"></span></p>
                                <p>
                                    <span class="font-medium text-gray-700">Visitante frecuente:</span>
                                    <span x-show="visitanteSeleccionado.visitas_count >= 5" class="text-yellow-600">★ Sí (5+ visitas)</span>
                                    <span x-show="!visitanteSeleccionado || visitanteSeleccionado.visitas_count < 5" class="text-gray-500">No</span>
                                </p>
                                {{-- Información de visita activa en el modal --}}
                                <div x-show="visitanteSeleccionado?.visita_activa" class="mt-3 p-3 bg-green-50 rounded-lg border border-green-200">
                                    <p class="text-sm font-medium text-green-800">Detalles de visita activa:</p>
                                    <p class="text-xs text-green-700 mt-1">
                                        Entrada: <span x-text="visitanteSeleccionado?.visita_activa?.fecha_hora_entrada ? new Date(visitanteSeleccionado.visita_activa.fecha_hora_entrada).toLocaleString('es-ES') : ''"></span>
                                    </p>
                                    <p class="text-xs text-green-700">
                                        Propósito: <span x-text="visitanteSeleccionado?.visita_activa?.proposito?.nombre || ''"></span>
                                    </p>
                                    <a href="{{ route('visitas.historial') }}?estado=activo"
                                       class="mt-2 inline-flex items-center text-xs text-green-700 hover:text-green-900 font-medium">
                                        Ir al historial de visitas activas
                                        <svg class="ml-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 rounded-b-xl flex justify-between items-center">
                        <button @click="eliminarVisitante(visitanteSeleccionado?.id)"
                                class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition flex items-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Eliminar
                        </button>
                        <button @click="cerrarModal" type="button"
                                class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 md:mt-8">
        {{ $visitantes->withQueryString()->links() }}
    </div>
</div>

@endsection
