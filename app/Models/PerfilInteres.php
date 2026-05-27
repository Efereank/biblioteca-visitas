<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerfilInteres extends Model
{
    protected $table = 'perfiles_interes';  

    protected $fillable = ['nombre'];

    public function subcategorias()
    {
        return $this->hasMany(SubcategoriaInteres::class);
    }
}
