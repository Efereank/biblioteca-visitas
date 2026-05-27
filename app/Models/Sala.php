<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sala extends Model
{
    protected $fillable = ['nombre', 'descripcion'];

    public function visitas()
    {
        return $this->hasMany(Visita::class);
    }

    public function users()
{
    return $this->belongsToMany(User::class, 'sala_user');
}
}
