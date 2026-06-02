<?php

namespace Database\Seeders;

use App\Models\PropositoVisita;
use Illuminate\Database\Seeder;

class PropositosVisitaSeeder extends Seeder
{
    public function run(): void
    {
        $propositos = [
            ['nombre' => 'Consulta en sala', 'color' => '#0EA5E9'],
            ['nombre' => 'Préstamo a domicilio', 'color' => '#6366F1'],
            ['nombre' => 'Devolución', 'color' => '#14B8A6'],
            ['nombre' => 'Actividad cultural', 'color' => '#F43F5E'],
            ['nombre' => 'Estudio', 'color' => '#8B5CF6'],
            ['nombre' => 'Investigación', 'color' => '#10B981'],
            ['nombre' => 'Otro', 'color' => '#141412'],
        ];

        foreach ($propositos as $proposito) {
            PropositoVisita::updateOrCreate(
                ['nombre' => $proposito['nombre']],
                ['color' => $proposito['color']]
            );
        }
    }
}
