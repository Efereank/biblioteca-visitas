<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parroquia extends Model
{
    protected $fillable = ['nombre', 'municipio_id'];

    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }
}
