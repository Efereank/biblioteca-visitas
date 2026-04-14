<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoVisitante;

class TiposVisitanteSeeder extends Seeder
{
    public function run(): void
    {
        TipoVisitante::insert([
            ['nombre' => 'Estudiante', 'color' => '#3B82F6'],
            ['nombre' => 'Docente', 'color' => '#10B981'],
            ['nombre' => 'Investigador', 'color' => '#8B5CF6'],
            ['nombre' => 'Público general', 'color' => '#F59E0B'],
        ]);
    }
}
