<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'nombre' => 'admin',
                'descripcion' => 'Administrador general del sistema. Tiene acceso completo a todos los modulos.',
            ],
            [
                'nombre' => 'secretaria',
                'descripcion' => 'Encargada de registrar y administrar permisos, solicitudes y documentacion.',
            ],
            [
                'nombre' => 'jefe_directo',
                'descripcion' => 'Director del establecimiento que aprueba o rechaza solicitudes de permiso.',
            ],
            [
                'nombre' => 'funcionario',
                'descripcion' => 'Usuario estandar que solicita permisos administrativos.',
            ],
        ];

        foreach ($roles as $rol) {
            Rol::firstOrCreate(['nombre' => $rol['nombre']], $rol);
        }
    }
}
