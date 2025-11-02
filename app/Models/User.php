<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    /**
     * Atributos asignables en masa
     */
    protected $fillable = [
        'nombres',
        'apellidos',
        'run',
        'correo_institucional',
        'cargo',
        'departamento',
        'password',
        'activo',
        'jefe_directo_id',
    ];

    /**
     * Atributos ocultos al serializar
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Atributos que deben ser casteados
     */
    protected $casts = [
        'activo' => 'boolean',
        'password' => 'hashed',
    ];

    /**
     * Relaciones
     */

    // 🔹 Jefe directo (relación recursiva)
    public function jefeDirecto(): BelongsTo
    {
        return $this->belongsTo(User::class, 'jefe_directo_id');
    }

    // 🔹 Subordinados (usuarios que dependen de este)
    public function subordinados(): HasMany
    {
        return $this->hasMany(User::class, 'jefe_directo_id');
    }

    // 🔹 Solicitudes hechas por este usuario
    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class, 'user_id');
    }

    // 🔹 Solicitudes que este usuario valida (como jefe o director)
    public function solicitudesValidadas(): HasMany
    {
        return $this->hasMany(Solicitud::class, 'validador_id');
    }

    // 🔹 Resoluciones que registró este usuario
    public function resoluciones(): HasMany
    {
        return $this->hasMany(Resolucion::class, 'user_id');
    }

    // 🔹 Rol del usuario
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    /**
     * Accesor útil para mostrar nombre completo
     */
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombres} {$this->apellidos}";
    }

    /**
     * Personaliza el nombre del identificador de autenticación
     */
    public function getAuthIdentifierName()
    {
        return 'correo_institucional';
    }

}
