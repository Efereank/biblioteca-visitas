<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoVisitante extends Model
{
    use HasFactory;

    protected $table = 'tipos_visitante';
    protected $fillable = ['nombre', 'color'];

    public function visitantes()
    {
        return $this->hasMany(Visitante::class);
    }
}
