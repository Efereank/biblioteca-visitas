<?php

namespace App\Services;

use App\Models\Visitante;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRService
{
    /**
     * Generar código QR para un visitante.
     */
    public function generarQR($visitanteId)
    {
        $visitante = Visitante::with('tipoVisitante')->findOrFail($visitanteId);
        $url = route('visitas.create', ['cedula' => $visitante->cedula]);

        $qr = QrCode::size(250)
            ->backgroundColor(255, 255, 255)
            ->color(0, 0, 0)
            ->margin(10)
            ->generate($url);

        return [
            'visitante' => $visitante,
            'qr' => $qr,
        ];
    }

    /**
     * Obtener datos para la vista del QR.
     */
    public function obtenerDatosQR($visitanteId)
    {
        $visitante = Visitante::with('tipoVisitante')->findOrFail($visitanteId);
        $url = route('visitas.create', ['cedula' => $visitante->cedula]);

        return [
            'visitante' => $visitante,
            'url' => $url,
        ];
    }
}
