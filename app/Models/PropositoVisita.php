<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropositoVisita extends Model
{
    use HasFactory;

    protected $table = 'propositos_visita';
    protected $fillable = ['nombre', 'color'];

    public function visitas()
    {
        return $this->hasMany(Visita::class, 'proposito_id');
    }
}
