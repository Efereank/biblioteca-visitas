<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visitante extends Model
{
    use HasFactory;

    protected $table = 'visitantes';

    protected $fillable = [
        'tipo_documento', 'cedula', 'nombres', 'apellidos', 'email', 'telefono',
        'genero', 'fecha_nacimiento', 'institucion', 'tipo_visitante_id',
        'nacionalidad', 'direccion', 'municipio', 'parroquia', 'ciudad',
        'codigo_postal', 'grado_instruccion', 'profesion', 'situacion_laboral',
        'institucion_educativa_laboral', 'perfil_interes', 'subcategoria_interes',
        'formato_preferido', 'idiomas_interes', 'fecha_registro',
        'discapacidad', 'necesidades_especiales', 'consentimiento_comunicacion',
        'observaciones', 'usuario_registrador_id',

            'representante_nombre',
            'representante_cedula',
            'representante_parentesco',
            'docente_id',
    ];

    // AGREGAR ESTO - Para que los accessors siempre se incluyan en JSON
    protected $appends = [
        'nombre_completo',
        'edad',
        'es_frecuente'
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_registro' => 'date',
        'fecha_ultima_modificacion' => 'datetime',
        'idiomas_interes' => 'array',
        'consentimiento_comunicacion' => 'boolean',
    ];

        public function setCedulaAttribute($value)
        {
            // Si es null o string vacío, guardar null (para menores sin identificación)
            if (is_null($value) || $value === '') {
                $this->attributes['cedula'] = null;
                return;
            }

            // Eliminar cualquier caracter que no sea número
            $cedulaLimpia = preg_replace('/[^0-9]/', '', $value);
            $this->attributes['cedula'] = $cedulaLimpia ?: null;
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

    public function getEdadAttribute()
    {
        return $this->fecha_nacimiento ? $this->fecha_nacimiento->age : null;
    }

        public static function generarCodigoTemporal($nombre, $apellido)
    {
        $iniciales = strtoupper(substr($nombre, 0, 1) . substr($apellido, 0, 1));
        $fecha = date('ymd');
        $random = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        return 'TMP-' . $iniciales . '-' . $fecha . '-' . $random;
    }

    // Relación con el docente
public function docente()
{
    return $this->belongsTo(Visitante::class, 'docente_id');
}

// Menores asociados a este visitante (si es docente)
public function menoresACargo()
{
    return $this->hasMany(Visitante::class, 'docente_id');
}
}
