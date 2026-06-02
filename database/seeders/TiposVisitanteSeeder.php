<?php

namespace Database\Seeders;

use App\Models\TipoVisitante;
use Illuminate\Database\Seeder;

class TiposVisitanteSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            ['nombre' => 'Estudiante', 'color' => '#3B82F6'],
            ['nombre' => 'Docente', 'color' => '#10B981'],
            ['nombre' => 'Investigador', 'color' => '#8B5CF6'],
            ['nombre' => 'Público general', 'color' => '#F59E0B'],
        ];

        foreach ($tipos as $tipo) {
            TipoVisitante::updateOrCreate(
                ['nombre' => $tipo['nombre']],
                ['color' => $tipo['color']]
            );
        }
    }
}
