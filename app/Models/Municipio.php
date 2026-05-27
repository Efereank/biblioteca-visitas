<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Municipio extends Model
{
    protected $fillable = ['nombre', 'capital'];

    public function parroquias()
    {
        return $this->hasMany(Parroquia::class);
    }

    public function ciudades()
    {
        return $this->hasMany(Ciudad::class);
    }
}
