<?php

namespace App\Services;

use App\Models\User;
use App\Models\Sala;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserService
{
    /**
     * Listar todos los usuarios.
     */
    public function listar()
    {
        return User::with('salas')->get();
    }

    /**
     * Validar datos del usuario.
     */
    public function validarDatos(array $datos, $userId = null)
    {
        $reglas = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email' . ($userId ? ',' . $userId : ''),
            'password' => $userId ? 'nullable|string|min:6' : 'required|string|min:6',
            'role' => 'required|in:admin,bibliotecario,recepcionista',
            'salas' => 'nullable|array',
            'salas.*' => 'exists:salas,id',
        ];

        return Validator::make($datos, $reglas);
    }

    /**
     * Crear un nuevo usuario.
     */
    public function crear(array $datos)
    {
        $user = User::create([
            'name' => $datos['name'],
            'email' => $datos['email'],
            'password' => Hash::make($datos['password']),
            'role' => $datos['role'],
        ]);

        if ($datos['role'] === 'bibliotecario' && !empty($datos['salas'])) {
            $user->salas()->sync($datos['salas']);
        }

        return $user;
    }

    /**
     * Actualizar un usuario existente.
     */
    public function actualizar($id, array $datos)
    {
        $user = User::findOrFail($id);

        $user->name = $datos['name'];
        $user->email = $datos['email'];
        $user->role = $datos['role'];

        if (!empty($datos['password'])) {
            $user->password = Hash::make($datos['password']);
        }

        $user->save();

        if ($datos['role'] === 'bibliotecario') {
            $user->salas()->sync($datos['salas'] ?? []);
        } else {
            $user->salas()->detach();
        }

        return $user;
    }

    /**
     * Eliminar un usuario.
     */
    public function eliminar($id, $currentUserId)
    {
        if ($id == $currentUserId) {
            throw new \Exception('No puedes eliminarte a ti mismo.');
        }

        return User::findOrFail($id)->delete();
    }
}
