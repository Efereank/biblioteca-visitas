<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Iniciar sesión.
     */
    public function login($email, $password, $remember = false)
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return ['success' => false, 'message' => 'Credenciales incorrectas.'];
        }

        Auth::login($user, $remember);

        return ['success' => true, 'user' => $user];
    }

    /**
     * Cerrar sesión.
     */
    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
    }

    /**
     * Verificar si el usuario actual es admin.
     */
    public function isAdmin()
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();

        if (method_exists($user, 'isAdmin')) {
            return (bool) $user->isAdmin();
        }

        if (isset($user->role) && is_string($user->role)) {
            return strtolower($user->role) === 'admin' || strtolower($user->role) === 'administrator';
        }

        if (isset($user->roles) && is_iterable($user->roles)) {
            foreach ($user->roles as $role) {
                if ((is_string($role) && in_array(strtolower($role), ['admin', 'administrator'])) ||
                    (is_object($role) && isset($role->name) && in_array(strtolower($role->name), ['admin', 'administrator']))) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Verificar si el usuario actual es bibliotecario.
     */
    public function isBibliotecario()
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();

        // Prefer explicit method on the model if available.
        if (method_exists($user, 'isBibliotecario')) {
            return (bool) $user->isBibliotecario();
        }

        // Fallback to common role fields: `role` string or `roles` relationship.
        if (isset($user->role) && is_string($user->role)) {
            return strtolower($user->role) === 'bibliotecario';
        }

        if (isset($user->roles) && is_iterable($user->roles)) {
            foreach ($user->roles as $role) {
                if ((is_string($role) && strtolower($role) === 'bibliotecario') ||
                    (is_object($role) && isset($role->name) && strtolower($role->name) === 'bibliotecario')) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Verificar si el usuario actual es recepcionista.
     */
    public function isRecepcionista()
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();

        // Prefer explicit method on the model if available.
        if (method_exists($user, 'isRecepcionista')) {
            return (bool) $user->isRecepcionista();
        }

        // Fallback to common role fields: `role` string or `roles` relationship.
        if (isset($user->role) && is_string($user->role)) {
            return strtolower($user->role) === 'recepcionista';
        }

        if (isset($user->roles) && is_iterable($user->roles)) {
            foreach ($user->roles as $role) {
                if ((is_string($role) && strtolower($role) === 'recepcionista') ||
                    (is_object($role) && isset($role->name) && strtolower($role->name) === 'recepcionista')) {
                    return true;
                }
            }
        }

        return false;
    }
}
