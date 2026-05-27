<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ciudad extends Model
{
    protected $table = 'ciudades'; // ← Agregar esta línea

    protected $fillable = ['nombre', 'municipio_id'];

    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }
}
