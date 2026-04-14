@extends('layouts.app')

@section('content')
<div class="space-y-8 md:space-y-10">
    {{-- Gráfico de Radar: Actividades --}}
    <div class="bg-white rounded-xl shadow-lg p-4 md:p-6">
        <h2 class="text-lg md:text-xl font-bold text-gray-800 mb-4">Actividades más realizadas</h2>
        <div class="h-72 md:h-96">
            <canvas id="radarChart"></canvas>
        </div>
        <p class="text-xs text-gray-500 mt-2 text-center">Cantidad de veces que cada actividad fue seleccionada en las visitas</p>
    </div>

    {{-- Gráfico de Flujo Horario --}}
    <div class="bg-white rounded-xl shadow-lg p-4 md:p-6">
        <h2 class="text-lg md:text-xl font-bold text-gray-800 mb-4">Flujo de visitas por hora (últimos 30 días)</h2>
        <div class="h-64 md:h-80">
            <canvas id="flujoHorarioChart"></canvas>
        </div>
        <p class="text-xs text-gray-500 mt-2 text-center">Horario de la biblioteca: 8:00 AM - 8:00 PM</p>
    </div>

    {{-- Gráfico de visitas por día de la semana --}}
    <div class="bg-white rounded-xl shadow-lg p-4 md:p-6">
        <h2 class="text-lg md:text-xl font-bold text-gray-800 mb-4">Visitas por día de la semana</h2>
        <div class="h-64 md:h-80">
            <canvas id="diasChart"></canvas>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.chartReportesData = {
        actividadesLabels: {!! json_encode($actividadesLabels) !!},
        actividadesData: {!! json_encode($actividadesData) !!},
        horasLabels: {!! json_encode($horasLabels) !!},
        flujoHorario: {!! json_encode($flujoHorario) !!},
        diasLabels: {!! json_encode($diasLabels) !!},
        diasData: {!! json_encode($diasData) !!}
    };
</script>
@endpush
@endsection
