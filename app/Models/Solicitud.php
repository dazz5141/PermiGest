<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Solicitud extends Model
{
    protected $table = 'solicitudes';

    protected $fillable = [
        'user_id',
        'tipo_solicitud_id',
        'estado_solicitud_id',
        'parentesco_id',
        'motivo',
        'fecha_desde',
        'fecha_hasta',
        'hora_desde',
        'hora_hasta',
        'dias_solicitados',
        'jornada',
        'tipo_varios',
        'observaciones',
        'validador_id',
        'fecha_envio',
        'fecha_revision',
        'observaciones_validador',
        'firma_validador',
        'token_validacion',
    ];

    protected $casts = [
        'fecha_desde'           => 'date',
        'fecha_hasta'           => 'date',
        'hora_desde'            => 'datetime:H:i',  // si es TIME en la DB funciona bien como string también
        'hora_hasta'            => 'datetime:H:i',
        'fecha_envio'           => 'datetime',
        'fecha_revision'        => 'datetime',
        'firma_validador'       => 'boolean',
        'dias_solicitados'      => 'float',
        'estado_solicitud_id'   => 'integer',
        'tipo_solicitud_id'     => 'integer',
        'parentesco_id'         => 'integer',
        'user_id'               => 'integer',
        'validador_id'          => 'integer',
    ];

    // 🔹 Relaciones

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(TipoSolicitud::class, 'tipo_solicitud_id');
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(EstadoSolicitud::class, 'estado_solicitud_id');
    }

    public function parentesco(): BelongsTo
    {
        return $this->belongsTo(Parentesco::class, 'parentesco_id');
    }

    public function validador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validador_id');
    }

    public function resoluciones(): HasMany
    {
        return $this->hasMany(Resolucion::class, 'solicitud_id');
    }
}
