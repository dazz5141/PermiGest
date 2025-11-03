<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Administrador general
        $admin = User::firstOrCreate(
            ['run' => '11.111.111-1'],
            [
                'nombres' => 'Administrador',
                'apellidos' => 'General',
                'correo_institucional' => 'admin@colegio.cl',
                'cargo' => 'Administrador General',
                'departamento' => 'Dirección',
                'password' => Hash::make('admin123'),
                'activo' => 1,
                'rol_id' => 1,
                'jefe_directo_id' => null,
            ]
        );

        // Secretaria
        $secretaria = User::firstOrCreate(
            ['run' => '22.222.222-2'],
            [
                'nombres' => 'María',
                'apellidos' => 'Secretaria',
                'correo_institucional' => 'secretaria@colegio.cl',
                'cargo' => 'Secretaria',
                'departamento' => 'Administración',
                'password' => Hash::make('secretaria123'),
                'activo' => 1,
                'rol_id' => 2,
                'jefe_directo_id' => $admin->id,
            ]
        );

        // Jefe directo (Inspector General)
        $jefe = User::firstOrCreate(
            ['run' => '33.333.333-3'],
            [
                'nombres' => 'Carlos',
                'apellidos' => 'Inspector',
                'correo_institucional' => 'jefe@colegio.cl',
                'cargo' => 'Inspector General',
                'departamento' => 'Convivencia Escolar',
                'password' => Hash::make('jefe123'),
                'activo' => 1,
                'rol_id' => 3,
                'jefe_directo_id' => $admin->id,
            ]
        );

        // Docente
        $docente = User::firstOrCreate(
            ['run' => '44.444.444-4'],
            [
                'nombres' => 'Ana',
                'apellidos' => 'Pérez',
                'correo_institucional' => 'docente@colegio.cl',
                'cargo' => 'Docente',
                'departamento' => 'Matemática',
                'password' => Hash::make('docente123'),
                'activo' => 1,
                'rol_id' => 4,
                'jefe_directo_id' => $jefe->id,
            ]
        );
    }
}
