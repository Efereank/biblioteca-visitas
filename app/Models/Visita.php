<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visita extends Model
{
    use HasFactory;

    protected $table = 'visitas';
    protected $fillable = [
        'visitante_id', 'proposito_id', 'fecha_hora_entrada',
        'fecha_hora_salida', 'observaciones', 'actividades_ids'
    ];

    protected $casts = [
        'fecha_hora_entrada' => 'datetime',
        'fecha_hora_salida' => 'datetime',
        'actividades_ids' => 'array',
    ];

    public function visitante()
    {
        return $this->belongsTo(Visitante::class);
    }

    public function proposito()
    {
        return $this->belongsTo(PropositoVisita::class, 'proposito_id');
    }

    public function actividades()
    {
        return Actividad::whereIn('id', $this->actividades_ids ?? [])->get();
    }

    //  cuando se crea una visita, incrementar el contador del visitante
    protected static function booted()
    {
        static::created(function ($visita) {
            $visita->visitante->incrementarVisitas();
        });
    }
}
