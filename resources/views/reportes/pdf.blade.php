<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Informe Estadístico - Biblioteca "María Calcaño"</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1f2937;
            margin: 0;
            padding: 30px;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #1e3a8a;
        }
        .header img {
            width: 240px;
            height: auto;
            margin-bottom: 10px;
        }
        .header .period {
            font-size: 12px;
            color: #6b7280;
            margin-top: 5px;
        }

        .resumen {
            margin: 30px 0;
            text-align: center;
        }
        .resumen-linea {
            display: inline-block;
            margin: 0 40px;
            vertical-align: top;
        }
        .resumen-cifra {
            font-size: 32px;
            font-weight: bold;
            color: #1e3a8a;
            line-height: 1;
        }
        .resumen-etiqueta {
            font-size: 11px;
            color: #4b5563;
            margin-top: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .highlights {
            margin: 25px 0;
            padding: 15px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
        }
        .highlights table {
            width: 100%;
        }
        .highlights td {
            padding: 6px 10px;
            font-size: 11px;
            vertical-align: top;
        }
        .highlight-label {
            color: #64748b;
        }
        .highlight-value {
            font-weight: bold;
            color: #1e3a8a;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1e3a8a;
            border-bottom: 1px solid #2563eb;
            padding-bottom: 4px;
            margin: 25px 0 10px 0;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data-table thead th {
            background-color: #1e3a8a;
            color: white;
            padding: 7px 10px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }
        table.data-table tbody td {
            padding: 6px 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10px;
        }
        table.data-table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        .notice {
            margin-top: 25px;
            font-size: 10px;
            color: #92400e;
            background: #fef3c7;
            border: 1px solid #f59e0b;
            padding: 10px;
            border-radius: 5px;
        }

        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #d1d5db;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('img/logo2.png') }}" alt="Logo">
        <div class="period">
            Período de análisis: {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
        </div>
    </div>

    <div class="resumen">
        <div class="resumen-linea">
            <div class="resumen-cifra">{{ $totalVisitas }}</div>
            <div class="resumen-etiqueta">Total de ingresos</div>
        </div>
        <div class="resumen-linea">
            <div class="resumen-cifra">{{ $promedioDiario }}</div>
            <div class="resumen-etiqueta">Media diaria de afluencia</div>
        </div>
    </div>



    <!-- Top 5 días -->
    @if(isset($topDias) && $topDias->isNotEmpty())
    <div class="section-title">Ranking de fechas con mayor demanda</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="10%">#</th>
                <th width="35%">Fecha</th>
                <th width="25%">Día</th>
                <th class="text-center" width="30%">Ingresos</th>
            </tr>
        </thead>
        <tbody>
            @php
                $diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            @endphp
            @foreach($topDias as $index => $dia)
            <tr>
                <td class="font-bold">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($dia->fecha)->format('d/m/Y') }}</td>
                <td>{{ $diasSemana[\Carbon\Carbon::parse($dia->fecha)->dayOfWeek] }}</td>
                <td class="text-center font-bold">{{ $dia->total }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- Índice de fidelización -->
    <div class="section-title">Índice de fidelización de usuarios</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Tipo de usuario</th>
                <th class="text-center">Ingresos</th>
                <th class="text-right">%</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Usuarios de primera visita</td>
                <td class="text-center font-bold">{{ $nuevosVisitas }}</td>
                <td class="text-right">{{ $pctNuevos }}%</td>
            </tr>
            <tr>
                <td>Usuarios recurrentes</td>
                <td class="text-center font-bold">{{ $recurrentesVisitas }}</td>
                <td class="text-right">{{ $pctRecurrentes }}%</td>
            </tr>
        </tbody>
    </table>

    <!-- Segmentación por género -->
    <div class="section-title">Segmentación por género</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Género</th>
                <th class="text-center">Ingresos</th>
                <th class="text-right">%</th>
            </tr>
        </thead>
        <tbody>
            @php $total = $totalVisitas ?: 1; @endphp
            @foreach($visitasPorGenero as $genero)
            <tr>
                <td>{{ $genero->genero }}</td>
                <td class="text-center font-bold">{{ $genero->total }}</td>
                <td class="text-right">{{ number_format(($genero->total / $total) * 100, 1) }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Segmentación por rango etario -->
    <div class="section-title">Segmentación por rango etario</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Grupo etario</th>
                <th class="text-center">Ingresos</th>
                <th class="text-right">%</th>
            </tr>
        </thead>
        <tbody>
            @foreach($visitasPorEdad as $grupo)
            <tr>
                <td>{{ $grupo->grupo_etario }}</td>
                <td class="text-center font-bold">{{ $grupo->total }}</td>
                <td class="text-right">{{ number_format(($grupo->total / $total) * 100, 1) }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Unidad de servicio (Sala) -->
    <div class="section-title">Distribución por unidad de servicio</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Sala</th>
                <th class="text-center">Ingresos</th>
                <th class="text-right">%</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salas as $sala)
            <tr>
                <td>{{ $sala->nombre }}</td>
                <td class="text-center font-bold">{{ $sala->visitas_count }}</td>
                <td class="text-right">{{ number_format(($sala->visitas_count / $total) * 100, 1) }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Categoría de usuario -->
    <div class="section-title">Distribución por categoría de usuario</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Categoría</th>
                <th class="text-center">Ingresos</th>
                <th class="text-right">%</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tipos as $tipo)
            <tr>
                <td>{{ $tipo->nombre }}</td>
                <td class="text-center font-bold">{{ $tipo->visitas_count }}</td>
                <td class="text-right">{{ number_format(($tipo->visitas_count / $total) * 100, 1) }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Motivo de consulta -->
    <div class="section-title">Distribución por motivo de consulta</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Propósito</th>
                <th class="text-center">Ingresos</th>
                <th class="text-right">%</th>
            </tr>
        </thead>
        <tbody>
            @foreach($propositos as $proposito)
            <tr>
                <td>{{ $proposito->nombre }}</td>
                <td class="text-center font-bold">{{ $proposito->visitas_count }}</td>
                <td class="text-right">{{ number_format(($proposito->visitas_count / $total) * 100, 1) }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="notice">
        <strong>Nota metodológica:</strong> El presente documento constituye un resumen estadístico. Para acceder al listado analítico completo de los {{ $totalVisitas }} registros, utilice la opción <strong>Exportar a Excel</strong>.
    </div>

    <div class="footer">
        Documento generado el {{ now()->format('d/m/Y H:i') }} — Sistema de Gestión Bibliotecaria "María Calcaño"
    </div>
</body>
</html>
