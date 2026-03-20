<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Rol::query()
            ->whereIn('nombre', ['admin', 'encargado_sistema', 'secretaria', 'jefe_directo', 'funcionario'])
            ->get()
            ->keyBy('nombre');

        $admin = User::firstOrCreate(
            ['run' => '11.111.111-1'],
            [
                'nombres' => 'Administrador',
                'apellidos' => 'General',
                'correo_institucional' => 'admin@colegio.cl',
                'cargo' => 'Administrador General',
                'departamento' => 'Direccion',
                'password' => Hash::make('admin123'),
                'activo' => 1,
                'rol_id' => $roles['admin']->id,
                'jefe_directo_id' => null,
            ]
        );

        User::firstOrCreate(
            ['run' => '12.345.678-5'],
            [
                'nombres' => 'Erika',
                'apellidos' => 'Soporte',
                'correo_institucional' => 'encargado@colegio.cl',
                'cargo' => 'Encargada del Sistema',
                'departamento' => 'Informatica',
                'password' => Hash::make('encargado123'),
                'activo' => 1,
                'rol_id' => $roles['encargado_sistema']->id,
                'jefe_directo_id' => $admin->id,
            ]
        );

        $secretaria = User::firstOrCreate(
            ['run' => '22.222.222-2'],
            [
                'nombres' => 'Maria',
                'apellidos' => 'Secretaria',
                'correo_institucional' => 'secretaria@colegio.cl',
                'cargo' => 'Secretaria',
                'departamento' => 'Administracion',
                'password' => Hash::make('secretaria123'),
                'activo' => 1,
                'rol_id' => $roles['secretaria']->id,
                'jefe_directo_id' => $admin->id,
            ]
        );

        $director = User::firstOrCreate(
            ['run' => '33.333.333-3'],
            [
                'nombres' => 'Carlos',
                'apellidos' => 'Director',
                'correo_institucional' => 'jefe@colegio.cl',
                'cargo' => 'Director del Establecimiento',
                'departamento' => 'Direccion',
                'password' => Hash::make('jefe123'),
                'activo' => 1,
                'rol_id' => $roles['jefe_directo']->id,
                'jefe_directo_id' => $admin->id,
            ]
        );

        User::firstOrCreate(
            ['run' => '44.444.444-4'],
            [
                'nombres' => 'Ana',
                'apellidos' => 'Perez',
                'correo_institucional' => 'docente@colegio.cl',
                'cargo' => 'Docente',
                'departamento' => 'Matematica',
                'password' => Hash::make('docente123'),
                'activo' => 1,
                'rol_id' => $roles['funcionario']->id,
                'jefe_directo_id' => $director->id,
            ]
        );
    }
}
