<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])] // ← Agregar 'role'
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relación con salas (solo bibliotecarios)
    public function salas()
    {
        return $this->belongsToMany(Sala::class, 'sala_user');
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    // Verificar si es bibliotecario
    public function isBibliotecario()
    {
        return $this->role === 'bibliotecario';
    }

    // Verificar si es recepcionista
    public function isRecepcionista()
    {
        return $this->role === 'recepcionista';
    }
}
