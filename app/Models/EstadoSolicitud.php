<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstadoSolicitud extends Model
{
    protected $table = 'estados_solicitud';
    protected $fillable = [
        'nombre'
    ];

    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class, 'estado_solicitud_id');
    }
}
