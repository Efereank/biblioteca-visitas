@extends('layouts.app')

@section('content')
<div class="space-y-6 md:space-y-8">
    {{-- Tarjetas grid responsive --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl shadow-lg p-5 md:p-6 transform hover:scale-105 transition-transform">
            <h3 class="text-base md:text-lg font-semibold opacity-90">Visitas Hoy</h3>
            <p class="text-3xl md:text-4xl font-bold mt-2">{{ $visitasHoy }}</p>
        </div>
        <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl shadow-lg p-5 md:p-6 transform hover:scale-105 transition-transform">
            <h3 class="text-base md:text-lg font-semibold opacity-90">Visitantes Activos</h3>
            <p class="text-3xl md:text-4xl font-bold mt-2">{{ $visitantesActivos }}</p>
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl shadow-lg p-5 md:p-6 transform hover:scale-105 transition-transform">
            <h3 class="text-base md:text-lg font-semibold opacity-90">Promedio Diario</h3>
            <p class="text-3xl md:text-4xl font-bold mt-2">{{ round($promedioDiario) }}</p>
        </div>
        <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white rounded-xl shadow-lg p-5 md:p-6 transform hover:scale-105 transition-transform">
            <h3 class="text-base md:text-lg font-semibold opacity-90">Frecuentes</h3>
            <p class="text-3xl md:text-4xl font-bold mt-2">{{ $frecuentes }}</p>
        </div>
    </div>

    {{-- Gráfico y tabla responsive --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 md:gap-8">
        <div class="bg-white rounded-xl shadow-lg p-4 md:p-6">
            <h2 class="text-lg md:text-xl font-bold text-gray-800 mb-4">Visitas por Tipo de Visitante</h2>
            <div class="h-64 md:h-80">
                <canvas id="chartTiposVisitante"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-4 md:p-6">
            <h2 class="text-lg md:text-xl font-bold text-gray-800 mb-4">Últimas Visitas</h2>
            <div class="overflow-x-auto -mx-4 md:mx-0">
                <div class="inline-block min-w-full align-middle">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 md:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Visitante</th>
                                <th class="px-3 md:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Entrada</th>
                                <th class="px-3 md:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Propósito</th>
                            </tr>
                        </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($ultimasVisitas as $visita)
                        <tr>
                            <td class="px-3 md:px-4 py-3 text-sm">
                                {{ $visita->visitante->nombre_completo }}
                                @if(!$visita->fecha_hora_salida)
                                    <span class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Activo
                                    </span>
                                @endif
                            </td>
                            <td class="px-3 md:px-4 py-3 text-sm">{{ $visita->fecha_hora_entrada->format('H:i') }}</td>
                            <td class="px-3 md:px-4 py-3 text-sm">
                                <span class="px-2 py-1 text-xs font-medium rounded-full text-white whitespace-nowrap"
                                    style="background-color: {{ $visita->proposito->color }}">
                                    {{ $visita->proposito->nombre }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.chartDashboardData = {
        tiposLabels: {!! json_encode($tiposLabels) !!},
        tiposData: {!! json_encode($tiposData) !!},
        tiposColors: {!! json_encode($tiposColors) !!}
    };
</script>
@endpush

@endsection
