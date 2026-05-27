@extends('layouts.app')

@section('content')
<div class="space-y-8 md:space-y-10">
    {{-- Filtros y exportaciones --}}
    <div class="bg-white rounded-xl shadow-lg p-4 md:p-6">
        <form method="GET" action="{{ route('reportes') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha inicio</label>
                <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio', now()->subMonth()->format('Y-m-d')) }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha fin</label>
                <input type="date" name="fecha_fin" value="{{ request('fecha_fin', now()->format('Y-m-d')) }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">Filtrar</button>
                <a href="{{ route('reportes.excel', request()->only(['fecha_inicio','fecha_fin'])) }}"
                   class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm flex items-center gap-1">
                    📊 Excel
                </a>
                <a href="{{ route('reportes.pdf', request()->only(['fecha_inicio','fecha_fin'])) }}"
                   class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 text-sm flex items-center gap-1">
                    📄 PDF
                </a>
            </div>
        </form>
        <div class="mt-3 flex gap-2">
            @php
                $trimestres = [
                    ['Q1', now()->startOfYear(), now()->startOfYear()->addMonths(2)->endOfMonth()],
                    ['Q2', now()->startOfYear()->addMonths(3), now()->startOfYear()->addMonths(5)->endOfMonth()],
                    ['Q3', now()->startOfYear()->addMonths(6), now()->startOfYear()->addMonths(8)->endOfMonth()],
                    ['Q4', now()->startOfYear()->addMonths(9), now()->startOfYear()->addMonths(11)->endOfMonth()],
                ];
            @endphp
            @foreach($trimestres as $t)
                <a href="?fecha_inicio={{ $t[1]->format('Y-m-d') }}&fecha_fin={{ $t[2]->format('Y-m-d') }}"
                   class="text-xs px-3 py-1 rounded border {{ request('fecha_inicio')==$t[1]->format('Y-m-d') ? 'bg-blue-100 border-blue-300' : '' }}">
                    {{ $t[0] }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Gráficos (existente) --}}
    <div class="bg-white rounded-xl shadow-lg p-4 md:p-6">
        <h2 class="text-lg md:text-xl font-bold text-gray-800 mb-4">Salas más visitadas</h2>
        <div class="h-72 md:h-96"><canvas id="radarChart"></canvas></div>
    </div>
    <div class="bg-white rounded-xl shadow-lg p-4 md:p-6">
        <h2 class="text-lg md:text-xl font-bold text-gray-800 mb-4">Flujo de visitas por hora</h2>
        <div class="h-64 md:h-80"><canvas id="flujoHorarioChart"></canvas></div>
    </div>
    <div class="bg-white rounded-xl shadow-lg p-4 md:p-6">
        <h2 class="text-lg md:text-xl font-bold text-gray-800 mb-4">Visitas por día de la semana</h2>
        <div class="h-64 md:h-80"><canvas id="diasChart"></canvas></div>
    </div>
</div>

@push('scripts')
<script>
    window.chartReportesData = {
        salasLabels: {!! json_encode($salasLabels) !!},
        salasData: {!! json_encode($salasData) !!},
        horasLabels: {!! json_encode($horasLabels) !!},
        flujoHorario: {!! json_encode($flujoHorario) !!},
        diasLabels: {!! json_encode($diasLabels) !!},
        diasData: {!! json_encode($diasData) !!}
    };
</script>
@endpush
@endsection
