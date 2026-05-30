<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoSolicitud extends Model
{
    public const CODIGO_CON_GOCE = 'con_goce';
    public const CODIGO_SIN_GOCE = 'sin_goce';
    public const CODIGO_DEFUNCION = 'defuncion';
    public const CODIGO_VARIOS = 'varios';

    protected $table = 'tipos_solicitud';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'protegido',
    ];

    protected $casts = [
        'protegido' => 'boolean',
    ];

    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class, 'tipo_solicitud_id');
    }
}
