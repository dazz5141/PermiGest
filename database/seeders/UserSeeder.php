<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use RuntimeException;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Rol::query()
            ->whereIn('nombre', ['admin', 'encargado_sistema', 'secretaria', 'jefe_directo', 'funcionario'])
            ->get()
            ->keyBy('nombre');

        $crearUsuariosDemo = app()->environment(['local', 'testing']);

        if (! $crearUsuariosDemo && User::where('rol_id', $roles['admin']->id)->exists()) {
            return;
        }

        $adminPassword = $crearUsuariosDemo ? 'admin123' : $this->initialAdminPassword();

        $admin = User::firstOrCreate(
            ['run' => '11.111.111-1'],
            [
                'nombres' => 'Administrador',
                'apellidos' => 'General',
                'correo_institucional' => 'admin@colegio.cl',
                'cargo' => 'Administrador General',
                'departamento' => 'Direccion',
                'password' => Hash::make($adminPassword),
                'activo' => 1,
                'rol_id' => $roles['admin']->id,
                'jefe_directo_id' => null,
            ]
        );

        if (! $crearUsuariosDemo) {
            return;
        }

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
                'password' => Hash::make('jefe1234'),
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

    private function initialAdminPassword(): string
    {
        $password = config('permigest.initial_admin_password');
        $validator = Validator::make(
            ['password' => $password],
            ['password' => ['required', 'string', Password::min(12)->letters()->numbers()]]
        );

        if ($validator->fails()) {
            throw new RuntimeException(
                'Configura PERMIGEST_INITIAL_ADMIN_PASSWORD con al menos 12 caracteres, letras y numeros antes del primer seed.'
            );
        }

        return $password;
    }
}
