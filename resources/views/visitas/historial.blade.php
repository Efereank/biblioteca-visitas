@extends('layouts.app')

@section('content')
<div class="bg-white rounded-xl shadow-lg overflow-hidden">
    {{-- Filtros colapsable --}}
    <div x-data="{ open: {{ request()->hasAny(['fecha_inicio', 'fecha_fin', 'proposito', 'estado', 'cedula']) ? 'true' : 'false' }} }" class="border-b">
        <button @click="open = !open" class="w-full px-4 md:px-6 py-3 md:py-4 text-left font-semibold flex justify-between items-center hover:bg-gray-50 transition">
            <span class="text-gray-800 text-sm md:text-base">
                Filtros de búsqueda
                @if(request()->hasAny(['fecha_inicio', 'fecha_fin', 'proposito', 'estado', 'cedula']))
                    <span class="ml-2 px-2 py-0.5 bg-blue-100 text-blue-800 text-xs rounded-full">Filtros activos</span>
                @endif
            </span>
            <svg :class="{'rotate-180': open}" class="w-5 h-5 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
        <div x-show="open" x-collapse class="px-4 md:px-6 py-4 bg-gray-50">
            <form method="GET" action="{{ route('visitas.historial') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- Filtro por Cédula --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cédula del visitante</label>
                    <input type="text" name="cedula" value="{{ request('cedula') }}"
                           placeholder="12345678"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    <p class="text-xs text-gray-500 mt-1">Buscar por número de cédula</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha inicio</label>
                    <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha fin</label>
                    <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Propósito</label>
                    <select name="proposito" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <option value="">Todos</option>
                        @foreach($propositos as $p)
                            <option value="{{ $p->id }}" {{ request('proposito') == $p->id ? 'selected' : '' }}>
                                {{ $p->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select name="estado" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <option value="">Todos</option>
                        <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activos</option>
                        <option value="finalizado" {{ request('estado') == 'finalizado' ? 'selected' : '' }}>Finalizados</option>
                    </select>
                </div>

                <div class="sm:col-span-2 lg:col-span-3 flex flex-col sm:flex-row gap-2 sm:gap-3 justify-end">
                    <button type="submit" class="w-full sm:w-auto bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition text-sm font-medium">
                        <svg class="inline-block h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Aplicar filtros
                    </button>
                    <a href="{{ route('visitas.historial') }}" class="w-full sm:w-auto px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition text-sm font-medium text-center">
                        <svg class="inline-block h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Limpiar filtros
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla responsive --}}
    <div class="overflow-x-auto">
        <div class="inline-block min-w-full align-middle">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visitante</th>
                        <th class="hidden sm:table-cell px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cédula</th>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entrada</th>
                        <th class="hidden md:table-cell px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Salida</th>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Propósito</th>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($visitas as $visita)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 md:px-6 py-4 text-sm font-medium text-gray-900">
                            {{ $visita->visitante->nombre_completo }}
                            <span class="sm:hidden block text-xs text-gray-500">{{ $visita->visitante->cedula }}</span>
                        </td>
                        <td class="hidden sm:table-cell px-3 md:px-6 py-4 text-sm text-gray-500">
                            {{ $visita->visitante->cedula }}
                        </td>
                        <td class="px-3 md:px-6 py-4 text-sm text-gray-500">
                            {{ $visita->fecha_hora_entrada->format('d/m/Y H:i') }}
                        </td>
                        <td class="hidden md:table-cell px-3 md:px-6 py-4 text-sm">
                            @if($visita->fecha_hora_salida)
                                {{ $visita->fecha_hora_salida->format('d/m/Y H:i') }}
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Activo
                                </span>
                            @endif
                        </td>
                        <td class="px-3 md:px-6 py-4 whitespace-nowrap">
                            <span class="px-2 md:px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full text-white"
                                  style="background-color: {{ $visita->proposito->color }}">
                                {{ $visita->proposito->nombre }}
                            </span>
                            <span class="md:hidden block mt-1">
                                @if(!$visita->fecha_hora_salida)
                                    <span class="text-yellow-600 text-xs">Activo</span>
                                @endif
                            </span>
                        </td>
                        <td class="px-3 md:px-6 py-4 text-sm font-medium">
                            @if(!$visita->fecha_hora_salida)
                                <form action="{{ route('visitas.salida', $visita) }}" method="POST" class="inline" id="form-salida-{{ $visita->id }}">
                                    @csrf
                                    <button type="button"
                                            class="bg-red-600 hover:bg-red-700 text-white font-bold py-1 md:py-1.5 px-2 md:px-3 rounded-md text-xs transition"
                                            onclick="confirmarSalida({{ $visita->id }}, '{{ addslashes($visita->visitante->nombre_completo) }}')">
                                        <span class="hidden sm:inline">Registrar Salida</span>
                                        <span class="sm:hidden">Salida</span>
                                    </button>
                                </form>
                            @else
                                <span class="text-gray-400 text-xs">Completado</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 md:py-12 text-center text-gray-500 text-sm md:text-base">
                            @if(request()->hasAny(['fecha_inicio', 'fecha_fin', 'proposito', 'estado', 'cedula']))
                                No se encontraron visitas con los filtros seleccionados.
                                <br>
                                <a href="{{ route('visitas.historial') }}" class="text-blue-600 hover:text-blue-800 mt-2 inline-block">
                                    Limpiar filtros
                                </a>
                            @else
                                No hay visitas registradas.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="px-4 md:px-6 py-4 border-t">
        {{ $visitas->withQueryString()->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
    async function confirmarSalida(id, nombre) {
        const result = await Swal.fire({
            title: '¿Registrar salida?',
            html: `¿Confirma que <strong>${nombre}</strong> está saliendo de la biblioteca?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, registrar salida',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        });

        if (result.isConfirmed) {
            // Mostrar loading
            Swal.fire({
                title: 'Registrando salida...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Enviar formulario
            document.getElementById(`form-salida-${id}`).submit();
        }
    }

    // Mostrar mensaje de éxito o error si viene en la URL
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const mensaje = urlParams.get('mensaje');

        if (mensaje) {
            Swal.fire({
                icon: 'success',
                title: '¡Completado!',
                text: decodeURIComponent(mensaje),
                confirmButtonColor: '#2563eb',
                confirmButtonText: 'Aceptar'
            });
        }

        @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: '{{ session('success') }}',
            confirmButtonColor: '#2563eb',
            confirmButtonText: 'Aceptar'
        });
        @endif

        @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '{{ session('error') }}',
            confirmButtonColor: '#2563eb',
            confirmButtonText: 'Aceptar'
        });
        @endif
    });
</script>
@endpush
