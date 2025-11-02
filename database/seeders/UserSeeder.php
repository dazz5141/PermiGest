<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 🔹 Usuario Administrador
        User::firstOrCreate(
            ['correo_institucional' => 'admin@colegio.cl'],
            [
                'nombres' => 'Administrador',
                'apellidos' => 'General',
                'run' => '11.111.111-1',
                'cargo' => 'Administrador General',
                'departamento' => 'Dirección',
                'password' => Hash::make('admin123'),
                'rol_id' => 1,
            ]
        );

        // 🔹 Secretaria
        User::firstOrCreate(
            ['correo_institucional' => 'secretaria@colegio.cl'],
            [
                'nombres' => 'María',
                'apellidos' => 'Secretaria',
                'run' => '22.222.222-2',
                'cargo' => 'Secretaria',
                'departamento' => 'Administración',
                'password' => Hash::make('secretaria123'),
                'rol_id' => 2,
            ]
        );

        // 🔹 Jefe Directo
        User::firstOrCreate(
            ['correo_institucional' => 'jefe@colegio.cl'],
            [
                'nombres' => 'Carlos',
                'apellidos' => 'Inspector',
                'run' => '33.333.333-3',
                'cargo' => 'Inspector General',
                'departamento' => 'Convivencia Escolar',
                'password' => Hash::make('jefe123'),
                'rol_id' => 3,
            ]
        );

        // 🔹 Funcionario (Docente)
        User::firstOrCreate(
            ['correo_institucional' => 'docente@colegio.cl'],
            [
                'nombres' => 'Ana',
                'apellidos' => 'Pérez',
                'run' => '44.444.444-4',
                'cargo' => 'Docente',
                'departamento' => 'Matemática',
                'password' => Hash::make('docente123'),
                'rol_id' => 4,
            ]
        );
    }
}
