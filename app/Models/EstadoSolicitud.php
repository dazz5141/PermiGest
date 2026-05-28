<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstadoSolicitud extends Model
{
    public const CODIGO_PENDIENTE = 'pendiente';
    public const CODIGO_APROBADO = 'aprobado';
    public const CODIGO_RECHAZADO = 'rechazado';
    public const CODIGO_ANULADO = 'anulado';

    protected $table = 'estados_solicitud';

    protected $fillable = [
        'codigo',
        'nombre',
        'protegido',
    ];

    protected $casts = [
        'protegido' => 'boolean',
    ];

    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class, 'estado_solicitud_id');
    }
}
