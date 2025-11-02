<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoSolicitud extends Model
{
    protected $table = 'tipos_solicitud';
    protected $fillable = [
        'nombre', 
        'descripcion'
    ];

    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class, 'tipo_solicitud_id');
    }
}
