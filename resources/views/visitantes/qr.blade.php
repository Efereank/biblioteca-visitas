@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Tarjeta QR para escanear --}}
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
        const qrCard = document.querySelector('.bg-white.p-4.rounded-xl.border-2.border-dashed').parentElement.innerHTML;
        const nombre = "{{ $visitante->nombre_completo }}";

        const ventana = window.open('', '_blank', 'width=400,height=550');
        ventana.document.write(`
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8"> <title>QR - ${nombre}</title>
                <style>
                    body {
                        text-align: center;
                        font-family: 'Segoe UI', sans-serif;
                        padding: 30px;
                    }
                    h2 { margin-bottom: 5px; }
                    p { color: #666; margin: 5px 0; }
                    svg { width: 300px; height: 300px; display: block; margin: 0 auto; }
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
            </body>
            </html>
        `);
        ventana.document.close();

        // Aumentamos ligeramente el tiempo para dar margen de renderizado
        setTimeout(() => ventana.print(), 800);
    }

    function imprimirTicket() {
        const ticket = document.getElementById('ticket-termico').innerHTML;
        const nombre = "{{ $visitante->nombre_completo }}";

        const ventana = window.open('', '_blank', 'width=350,height=550');
        ventana.document.write(`
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8"> <title>Ticket - ${nombre}</title>
                <style>
                    body {
                        font-family: 'Courier New', monospace;
                        font-size: 11px;
                        text-align: center;
                        margin: 0;
                        padding: 10px; /* Reducido para evitar cortes */
                        background: white;
                        color: black;
                        word-wrap: break-word; /* Evita que el texto largo se corte */
                    }
                    /* Forzamos el renderizado del SVG para la impresora térmica */
                    svg {
                        width: 160px !important;
                        height: 160px !important;
                        display: block !important;
                        margin: 0 auto !important;
                    }
                    img {
                        max-width: 160px;
                        margin: 0 auto;
                        display: block;
                    }
                    @media print {
                        @page {
                            size: 80mm auto;
                            margin: 0;
                        }
                        body {
                            margin: 0;
                            padding: 2mm; /* Margen mínimo para la térmica */
                        }
                    }
                </style>
            </head>
            <body>
                ${ticket}
            </body>
            </html>
        `);
        ventana.document.close();

        setTimeout(() => ventana.print(), 800);
    }
</script>
@endpush

<style>
    /* Estilos para el QR */
    .bg-white.p-4.rounded-xl.border-2.border-dashed svg {
        width: 220px !important;
        height: 220px !important;
    }

    #ticket-termico svg {
        width: 180px !important;
        height: 180px !important;
    }

    /* Animación sutil al cargar */
    .bg-white.rounded-xl.shadow-lg {
        transition: transform 0.2s ease;
    }
    .bg-white.rounded-xl.shadow-lg:hover {
        transform: translateY(-2px);
    }
</style>
@endsection
