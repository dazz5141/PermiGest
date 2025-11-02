<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Parentesco extends Model
{
    protected $table = 'parentescos';
    protected $fillable = [
        'nombre', 
        'observacion'
    ];

    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class, 'parentesco_id');
    }
}
