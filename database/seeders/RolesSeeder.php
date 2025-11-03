<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol; // ✅ usa tu modelo real

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'nombre' => 'admin',
                'descripcion' => 'Administrador general del sistema. Tiene acceso completo a todos los módulos.',
            ],
            [
                'nombre' => 'secretaria',
                'descripcion' => 'Encargada de registrar y administrar permisos, solicitudes y documentación.',
            ],
            [
                'nombre' => 'jefe_directo',
                'descripcion' => 'Valida o rechaza permisos de su equipo (por ejemplo, Inspector General).',
            ],
            [
                'nombre' => 'funcionario',
                'descripcion' => 'Usuario estándar que solicita permisos (por ejemplo, docentes).',
            ],
        ];

        foreach ($roles as $rol) {
            Rol::firstOrCreate(['nombre' => $rol['nombre']], $rol);
        }
    }
}
