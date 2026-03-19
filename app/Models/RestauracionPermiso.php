<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestauracionPermiso extends Model
{
    protected $table = 'restauraciones_permiso';

    protected $fillable = [
        'solicitud_id',
        'user_id',
        'tipo',
        'dias_restaurados',
        'motivo',
        'observacion',
        'documento_referencia',
    ];

    protected $casts = [
        'solicitud_id' => 'integer',
        'user_id' => 'integer',
        'dias_restaurados' => 'float',
    ];

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class, 'solicitud_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
