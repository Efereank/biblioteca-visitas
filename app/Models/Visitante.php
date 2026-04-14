<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visitante extends Model
{
    use HasFactory;

    protected $table = 'visitantes';

    protected $fillable = [
        'cedula', 'nombres', 'apellidos', 'email', 'telefono',
        'genero', 'fecha_nacimiento', 'institucion', 'tipo_visitante_id'
    ];

    // limpiar la cédula antes de guardar
    public function setCedulaAttribute($value)
    {
        // Eliminar cualquier caracter que no sea número
        $cedulaLimpia = preg_replace('/[^0-9]/', '', $value);
        $this->attributes['cedula'] = $cedulaLimpia;
    }

    public function tipoVisitante()
    {
        return $this->belongsTo(TipoVisitante::class);
    }

    public function visitas()
    {
        return $this->hasMany(Visita::class);
    }

    public function getNombreCompletoAttribute()
    {
        return "{$this->nombres} {$this->apellidos}";
    }

    public function getEsFrecuenteAttribute()
    {
        return $this->visitas_count >= 5;
    }

    public function incrementarVisitas()
    {
        $this->increment('visitas_count');
    }
}
