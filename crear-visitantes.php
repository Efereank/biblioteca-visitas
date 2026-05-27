<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Visitante;
use Carbon\Carbon;

$nombres = ['María', 'José', 'Luis', 'Ana', 'Carlos', 'Pedro', 'Sofía', 'Diego', 'Valentina', 'Gabriel', 'Isabella', 'Mateo', 'Camila', 'Daniel', 'Lucía', 'Jesús', 'Andrea', 'Sebastián', 'Victoria', 'David'];
$apellidos = ['González', 'Rodríguez', 'Pérez', 'Martínez', 'García', 'López', 'Hernández', 'Díaz', 'Torres', 'Ramírez', 'Flores', 'Morales', 'Rojas', 'Reyes', 'Castillo', 'Medina'];

for ($i = 1; $i <= 300; $i++) {
    $nombre = $nombres[array_rand($nombres)];
    $apellido = $apellidos[array_rand($apellidos)];
    
    Visitante::create([
        'tipo_documento' => 'C.I.',
        'cedula' => str_pad(mt_rand(1000000, 40000000), 8, '0', STR_PAD_LEFT),
        'nombres' => $nombre,
        'apellidos' => $apellido . ' ' . $apellidos[array_rand($apellidos)],
        'genero' => mt_rand(0, 1) ? 'M' : 'F',
        'fecha_nacimiento' => Carbon::now()->subYears(mt_rand(5, 70)),
        'nacionalidad' => 'Venezolana',
        'tipo_visitante_id' => mt_rand(1, 3),
        'fecha_registro' => Carbon::now()->subDays(mt_rand(0, 365)),
        'usuario_registrador_id' => 1,
    ]);
    
    echo "Visitante $i creado\n";
}

echo "¡300 visitantes creados exitosamente!\n";
