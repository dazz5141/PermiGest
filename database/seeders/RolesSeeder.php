<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['nombre' => 'admin', 'descripcion' => 'Administrador general del sistema.'],
            ['nombre' => 'secretaria', 'descripcion' => 'Encargada de registrar y administrar permisos.'],
            ['nombre' => 'jefe_directo', 'descripcion' => 'Valida o rechaza permisos de su equipo.'],
            ['nombre' => 'funcionario', 'descripcion' => 'Usuario estándar que solicita permisos.'],
        ];

        DB::table('roles')->insert($roles);
    }
}
