<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodoAdministrativo extends Model
{
    protected $table = 'periodos_administrativos';

    protected $fillable = [
        'anio',
        'fecha_inicio',
        'fecha_termino',
        'activo',
    ];

    protected $casts = [
        'fecha_inicio'  => 'date',
        'fecha_termino' => 'date',
        'activo'        => 'boolean',
    ];

    /**
     * Un período tiene muchas solicitudes
     */
    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class, 'periodo_id');
    }

    /**
     * Obtener el período activo (helper central)
     */
    public static function activo()
    {
        return self::where('activo', true)->first();
    }
}
