@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="h-2" style="background-color: {{ $visitante->tipoVisitante->color ?? '#2563eb' }}"></div>
            <div class="p-6 text-center">
                <div class="mb-4">
                    <h2 class="text-xl font-bold text-gray-800">{{ $visitante->nombre_completo }}</h2>
                    <p class="text-sm text-gray-600 mt-1">
                        {{ $visitante->tipo_documento ?? 'C.I.' }}: {{ $visitante->cedula }}
                    </p>
                    @if($visitante->tipoVisitante)
                    <span class="inline-block mt-2 px-3 py-1 rounded-full text-xs font-medium text-white"
                          style="background-color: {{ $visitante->tipoVisitante->color }}">
                        {{ $visitante->tipoVisitante->nombre }}
                    </span>
                    @endif
                </div>

                <div class="flex justify-center mb-4">
                    <div class="bg-white p-4 rounded-xl border-2 border-dashed border-gray-300 inline-block">
                        {!! $qr !!}
                    </div>
                </div>

                <p class="text-xs text-gray-500 mb-4">Escanee este código para registrar una nueva visita</p>

                <div class="flex flex-col sm:flex-row justify-center gap-2">
                    <button onclick="imprimirQR()"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                         Imprimir QR
                    </button>
                    <button onclick="imprimirTicket()"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-medium">
                        Imprimir Ticket
                    </button>
                    <a href="{{ route('visitas.create', ['cedula' => $visitante->cedula]) }}"
                       class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm font-medium text-center">
                         Nueva Visita
                    </a>
                </div>
            </div>
        </div>

        {{-- Información del visitante --}}
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="h-2" style="background-color: {{ $visitante->tipoVisitante->color ?? '#2563eb' }}"></div>
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Datos del Visitante</h3>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Nombre completo:</span>
                        <span class="font-medium">{{ $visitante->nombre_completo }}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Cédula:</span>
                        <span class="font-medium">{{ $visitante->cedula }}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Tipo:</span>
                        <span class="font-medium">{{ $visitante->tipoVisitante->nombre ?? 'N/D' }}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Email:</span>
                        <span class="font-medium">{{ $visitante->email ?? 'No registrado' }}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Teléfono:</span>
                        <span class="font-medium">{{ $visitante->telefono ?? 'No registrado' }}</span>
                    </div>
                    <div class="flex justify-between pb-2">
                        <span class="text-gray-600">Total visitas:</span>
                        <span class="font-medium">{{ $visitante->visitas_count ?? 0 }}</span>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t text-center">
                    <p class="text-xs text-gray-400">
                        Código generado: {{ now()->format('d/m/Y H:i') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Ticket térmico (oculto para impresión) --}}
    <div id="ticket-termico" style="display:none;">
        <div style="max-width: 280px; margin: 0 auto; font-family: 'Courier New', monospace; font-size: 11px; text-align: center;">
            <p style="font-weight: bold; font-size: 14px; margin-bottom: 2px;">BIBLIOTECA PÚBLICA DEL ZULIA</p>
            <p style="font-size: 12px; margin-bottom: 2px;">"María Calcaño"</p>
            <p style="font-size: 9px; margin-bottom: 8px;">{{ date('d/m/Y H:i') }}</p>
            <hr style="border: 1px dashed #000; margin: 8px 0;">
            <p style="margin: 3px 0;"><strong>Visitante:</strong> {{ $visitante->nombre_completo }}</p>
            <p style="margin: 3px 0;"><strong>Cédula:</strong> {{ $visitante->cedula }}</p>
            <p style="margin: 3px 0;"><strong>Tipo:</strong> {{ $visitante->tipoVisitante->nombre ?? 'N/D' }}</p>
            <div style="margin: 12px 0; display: flex; justify-content: center;">
                {!! $qr !!}
            </div>
            <hr style="border: 1px dashed #000; margin: 8px 0;">
            <p style="font-size: 9px;">Escanee al ingresar a la biblioteca</p>
            <p style="font-size: 8px; margin-top: 5px;">www.bibliotecapublicadelzulia.org</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function imprimirQR() {
        const elementoQR = document.querySelector('.bg-white.p-4.rounded-xl.border-2.border-dashed');
        if (!elementoQR) return;

        const qrCard = elementoQR.parentElement.innerHTML;
        const nombre = "{{ $visitante->nombre_completo }}";

        const ventana = window.open('', '_blank', 'width=400,height=550');

        // Validación contra bloqueadores de pop-ups
        if (!ventana) {
            alert("Por favor, permite las ventanas emergentes para poder imprimir.");
            return;
        }

        ventana.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>QR - ${nombre}</title>
                <style>
                    body {
                        text-align: center;
                        font-family: 'Segoe UI', sans-serif;
                        padding: 30px;
                        margin: 0;
                    }
                    h2 { margin-bottom: 5px; }
                    p { color: #666; margin: 5px 0; }
                    /* Asegura que el SVG herede el tamaño correcto en la impresión */
                    svg { width: 300px !important; height: 300px !important; display: inline-block; }
                    @media print {
                        @page { margin: 0; }
                        body { margin: 0; padding: 20px; }
                    }
                </style>
            </head>
            <body>
                <h2>${nombre}</h2>
                <p>Cédula: {{ $visitante->cedula }}</p>
                <div style="margin: 20px auto; display: inline-block;">
                    ${qrCard}
                </div>
                <p style="font-size: 12px; color: #999;">Escanee para registrar visita</p>

                <script>
                    window.addEventListener('DOMContentLoaded', () => {
                        window.print();
                        window.onafterprint = () => window.close();
                    });
                <\/script>
            </body>
            </html>
        `);
        ventana.document.close();
    }

    function imprimirTicket() {
        const elementoTicket = document.getElementById('ticket-termico');
        if (!elementoTicket) return;

        const ticket = elementoTicket.innerHTML;
        const nombre = "{{ $visitante->nombre_completo }}";

        const ventana = window.open('', '_blank', 'width=350,height=550');

        // Validación contra bloqueadores de pop-ups
        if (!ventana) {
            alert("Por favor, permite las ventanas emergentes para poder imprimir el ticket.");
            return;
        }

        ventana.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Ticket - ${nombre}</title>
                <style>
                    body {
                        font-family: 'Courier New', monospace;
                        font-size: 11px;
                        text-align: center;
                        margin: 0;
                        padding: 15px;
                        background: white;
                        width: 72mm; /* Ancho seguro para impresoras térmicas de 80mm */
                    }
                    /* Forzamos el tamaño del SVG del ticket */
                    svg { width: 180px !important; height: 180px !important; display: inline-block; }
                    @media print {
                        @page {
                            size: 80mm auto;
                            margin: 0;
                        }
                        body {
                            margin: 0;
                            padding: 5mm;
                        }
                    }
                </style>
            </head>
            <body>
                ${ticket}

                <script>
                    window.addEventListener('DOMContentLoaded', () => {
                        window.print();
                        window.onafterprint = () => window.close();
                    });
                <\/script>
            </body>
            </html>
        `);
        ventana.document.close();
    }
</script>
@endpush

<style>
    /* Configuración para el navegador / vista previa */
    body {
        font-family: 'Courier New', monospace;
        font-size: 12px; /* Tamaño ideal legible para agujas/térmicas */
        line-height: 1.2;
        text-align: center;
        margin: 0;
        padding: 0;
        background: white;
        width: 72mm; /* El ancho real imprimible de un papel de 80mm */
    }

    /* Forzar que los gráficos no desborden el rodillo */
    svg, img {
        max-width: 100% !important;
        height: auto !important;
        display: inline-block;
    }

    /* Ajustes específicos de impresión física */
    @media print {
        @page {
            size: 80mm auto; /* Define el ancho fijo y alto dinámico según contenido */
            margin: 0mm;    /* Elimina encabezados de página (fecha, url, título) */
        }
        body {
            margin: 0;
            padding: 0mm 2mm 5mm 2mm; /* Margen superior cero, inferior 5mm para el corte */
            width: 72mm;
        }
    }
</style>

@endsection
