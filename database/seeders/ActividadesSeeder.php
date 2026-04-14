<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Actividad;

class ActividadesSeeder extends Seeder
{
    public function run(): void
    {
        Actividad::insert([
            ['nombre' => 'Lectura en sala'],
            ['nombre' => 'Uso de computadoras'],
            ['nombre' => 'Consulta de catálogo'],
            ['nombre' => 'Taller/Charla'],
            ['nombre' => 'Sala infantil'],
            ['nombre' => 'Sala Braille'],
            ['nombre' => 'Fonoteca'],
            ['nombre' => 'Videoteca'],
            ['nombre' => 'Otro'],

            ]);

    }
}

