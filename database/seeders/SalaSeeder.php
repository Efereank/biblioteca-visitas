<?php

namespace Database\Seeders;

use App\Models\Sala;
use Illuminate\Database\Seeder;

class SalaSeeder extends Seeder
{
    public function run(): void
    {
        $salas = [
            'Sala de Referencia "David Belloso Rossell"',
            'Centro de Documentación Ambiental "Lago de Maracaibo"',
            'Sala de Conferencias "Hesnor Rivera"',
            'Sala Braille "Miguel Ángel Jusayú"',
            'Sala Infantil "Amenodoro Urdaneta"',
            'Sala Digital "Dr. Humberto Fernández Morán"',
            'Sala Hemeroteca "Eduardo López Rivas"',
            'Salón de Usos Múltiples "Dr. Américo Gollo Chávez"',
            'Salón Héctor R. Rojas (Robótica)',
            'Sala General de Lectura "María Calcaño"',
            'Sala Fonoteca "Ulises Acosta"',
            'Sala Vídeoteca "Manuel Trujillo Durán"',
        ];
        foreach ($salas as $nombre) {
            Sala::create(['nombre' => $nombre]);
        }
    }
}
