<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Municipio;
use App\Models\Parroquia;
use App\Models\Ciudad;

class ZuliaSeeder extends Seeder
{
    public function run(): void
    {
        $municipios = [
            'Almirante Padilla' => [
                'capital' => 'El Toro',
                'parroquias' => ['Isla de Toas', 'Monagas'],
                'ciudades' => ['El Toro'],
            ],
            'Baralt' => [
                'capital' => 'San Timoteo',
                'parroquias' => ['San Timoteo', 'General Urdaneta', 'Libertador', 'Manuel Guanipa Matos', 'Marcelino Briceño', 'Pueblo Nuevo'],
                'ciudades' => ['San Timoteo', 'Mene Grande'],
            ],
            'Cabimas' => [
                'capital' => 'Cabimas',
                'parroquias' => ['Ambrosio', 'Carmen Herrera', 'Germán Ríos Linares', 'Jorge Hernández', 'La Rosa', 'Punta Gorda', 'Rómulo Betancourt', 'San Benito', 'Arístides Calvani'],
                'ciudades' => ['Cabimas'],
            ],
            'Catatumbo' => [
                'capital' => 'Encontrados',
                'parroquias' => ['Encontrados', 'Udón Pérez'],
                'ciudades' => ['Encontrados'],
            ],
            'Colón' => [
                'capital' => 'San Carlos del Zulia',
                'parroquias' => ['San Carlos del Zulia', 'Santa Cruz del Zulia', 'Urribarrí', 'Moralito', 'San José'],
                'ciudades' => ['San Carlos del Zulia', 'Santa Cruz del Zulia'],
            ],
            'Francisco Javier Pulgar' => [
                'capital' => 'Pueblo Nuevo - El Chivo',
                'parroquias' => ['Carlos Quevedo', 'Francisco Javier Pulgar', 'Simón Rodríguez'],
                'ciudades' => ['Pueblo Nuevo - El Chivo'],
            ],
            'Guajira' => [
                'capital' => 'Sinamaica',
                'parroquias' => ['Sinamaica', 'Alta Guajira', 'Elías Sánchez Rubio', 'Guajira'],
                'ciudades' => ['Sinamaica', 'Paraguaipoa'],
            ],
            'Jesús Enrique Lossada' => [
                'capital' => 'La Concepción',
                'parroquias' => ['La Concepción', 'San José', 'Mariano Parra León', 'José Ramón Yépez'],
                'ciudades' => ['La Concepción'],
            ],
            'Jesús María Semprún' => [
                'capital' => 'Casigua - El Cubo',
                'parroquias' => ['Casigua - El Cubo', 'Barí'],
                'ciudades' => ['Casigua - El Cubo'],
            ],
            'La Cañada de Urdaneta' => [
                'capital' => 'Concepción',
                'parroquias' => ['Concepción', 'Andrés Bello', 'Chiquinquirá', 'El Carmelo', 'Potreritos'],
                'ciudades' => ['Concepción'],
            ],
            'Lagunillas' => [
                'capital' => 'Ciudad Ojeda',
                'parroquias' => ['Ciudad Ojeda', 'Alonso de Ojeda', 'Campo Lara', 'El Danto', 'Libertad', 'Venezuela'],
                'ciudades' => ['Ciudad Ojeda', 'Lagunillas'],
            ],
            'Machiques de Perijá' => [
                'capital' => 'Machiques',
                'parroquias' => ['Machiques', 'Bartolomé de las Casas', 'Libertad', 'Río Negro', 'San José de Perijá'],
                'ciudades' => ['Machiques'],
            ],
            'Mara' => [
                'capital' => 'San Rafael de El Moján',
                'parroquias' => ['San Rafael de El Moján', 'Luis de Vicente', 'Mons. Marcos Sergio Godoy', 'Ricaurte', 'Tamare'],
                'ciudades' => ['San Rafael de El Moján'],
            ],
            'Maracaibo' => [
                'capital' => 'Maracaibo',
                'parroquias' => ['Antonio Borjas Romero', 'Bolívar', 'Cacique Mara', 'Caracciolo Parra Pérez', 'Cecilio Acosta', 'Chiquinquirá', 'Coquivacoa', 'Cristo de Aranza', 'Francisco Eugenio Bustamante', 'Idelfonso Vásquez', 'Juana de Ávila', 'Luis Hurtado Higuera', 'Manuel Dagnino', 'Olegario Villalobos', 'Raúl Leoni', 'Santa Lucía', 'San Isidro', 'Venancio Pulgar'],
                'ciudades' => ['Maracaibo'],
            ],
            'Miranda' => [
                'capital' => 'Los Puertos de Altagracia',
                'parroquias' => ['Los Puertos de Altagracia', 'Ana María Campos', 'San Antonio', 'San José', 'Faria'],
                'ciudades' => ['Los Puertos de Altagracia'],
            ],
            'Rosario de Perijá' => [
                'capital' => 'La Villa del Rosario',
                'parroquias' => ['La Villa del Rosario', 'Donaldo García', 'Sixto Zambrano'],
                'ciudades' => ['La Villa del Rosario'],
            ],
            'San Francisco' => [
                'capital' => 'San Francisco',
                'parroquias' => ['San Francisco', 'Domitila Flores', 'El Bajo', 'Los Cortijos', 'Francisco Ochoa'],
                'ciudades' => ['San Francisco'],
            ],
            'Santa Rita' => [
                'capital' => 'Santa Rita',
                'parroquias' => ['Santa Rita', 'El Mene', 'José Cenobio Urribarrí', 'Pedro Lucas Urribarrí'],
                'ciudades' => ['Santa Rita'],
            ],
            'Simón Bolívar' => [
                'capital' => 'Tía Juana',
                'parroquias' => ['Tía Juana', 'Rafael María Baralt', 'Manuel Manrique'],
                'ciudades' => ['Tía Juana'],
            ],
            'Sucre' => [
                'capital' => 'Bobures',
                'parroquias' => ['Bobures', 'El Batey', 'Gibraltar', 'Heras', 'Monseñor Arturo Álvarez', 'Rómulo Gallegos'],
                'ciudades' => ['Bobures', 'Gibraltar'],
            ],
            'Valmore Rodríguez' => [
                'capital' => 'Bachaquero',
                'parroquias' => ['Bachaquero', 'La Victoria', 'Raúl Cuenca'],
                'ciudades' => ['Bachaquero'],
            ],
        ];

        foreach ($municipios as $nombre => $data) {
            $municipio = Municipio::create([
                'nombre' => $nombre,
                'capital' => $data['capital'],
            ]);

            foreach ($data['parroquias'] as $parroquia) {
                Parroquia::create([
                    'nombre' => $parroquia,
                    'municipio_id' => $municipio->id,
                ]);
            }

            foreach ($data['ciudades'] as $ciudad) {
                Ciudad::create([
                    'nombre' => $ciudad,
                    'municipio_id' => $municipio->id,
                ]);
            }
        }
    }
}
