<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Sala;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Admin existente (asumiendo id=1)
        $admin = User::find(1);
        if ($admin) {
            $admin->role = 'admin';
            $admin->save();
        }

        // Crear recepcionista de ejemplo
        User::firstOrCreate(
            ['email' => 'recepcion@biblioteca.com'],
            [
                'name' => 'Recepcionista',
                'password' => bcrypt('recepcion123'),
                'role' => 'recepcionista'
            ]
        );

        // Crear bibliotecario de ejemplo y asignarle salas 1 y 2
        $bibliotecario = User::firstOrCreate(
            ['email' => 'biblio@biblioteca.com'],
            [
                'name' => 'Bibliotecario Sala',
                'password' => bcrypt('biblio123'),
                'role' => 'bibliotecario'
            ]
        );
        // Asignar salas (ids 1 y 2)
        $bibliotecario->salas()->sync([1, 2]);
    }
}
