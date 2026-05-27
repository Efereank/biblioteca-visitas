<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubcategoriaInteres extends Model
{
    protected $table = 'subcategorias_interes';

    protected $fillable = ['nombre', 'perfil_interes_id'];

    public function perfil()
    {
        return $this->belongsTo(PerfilInteres::class, 'perfil_interes_id');
    }
}
