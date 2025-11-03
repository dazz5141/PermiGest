<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoVario extends Model
{
    use HasFactory;

    protected $table = 'tipos_varios';

    protected $fillable = ['nombre', 'descripcion'];

    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class, 'tipo_vario_id');
    }
}
