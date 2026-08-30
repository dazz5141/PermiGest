<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AuditoriaHelper;
use App\Http\Controllers\Controller;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::with('rol', 'jefeDirecto')->orderBy('nombres')->get();
        $roles = $this->obtenerRolesGestionables();
        $jefes = $this->obtenerDirectoresActivos();

        return view('admin.usuarios.index', compact('usuarios', 'roles', 'jefes'));
    }

    public function store(Request $request)
    {
        $rolesGestionablesIds = $this->obtenerRolesGestionables()->pluck('id')->all();

        $validated = $request->validate([
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'run' => 'required|string|max:15|unique:users,run',
            'correo_institucional' => 'required|email|max:150|unique:users,correo_institucional',
            'cargo' => 'nullable|string|max:100',
            'departamento' => 'nullable|string|max:100',
            'rol_id' => ['required', 'exists:roles,id', Rule::in($rolesGestionablesIds)],
            'jefe_directo_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('rol_id', $this->obtenerRolDirectorId())),
            ],
            'password' => $this->passwordRules(),
        ], $this->passwordMessages());

        try {
            $validated['run'] = $this->formatearRun($validated['run']);
        } catch (\Exception $e) {
            return back()->withErrors(['run' => 'El RUN ingresado no es valido.'])->withInput();
        }

        $this->validarUnicidadDirector((int) $validated['rol_id']);

        $validated['password'] = Hash::make($validated['password']);
        $validated['activo'] = true;

        $nuevo = User::create($validated);

        AuditoriaHelper::registrar('users', $nuevo->id, 'usuario_creado', Auth::user()->id, null, $nuevo->toArray());

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit($id)
    {
        $usuario = User::findOrFail($id);
        $roles = $this->obtenerRolesGestionables();
        $jefes = $this->obtenerDirectoresActivos($usuario->id);

        if (! $this->puedeGestionarUsuario($usuario)) {
            abort(403, 'No tienes permiso para editar este usuario.');
        }

        return view('admin.usuarios.edit', compact('usuario', 'roles', 'jefes'));
    }

    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        if (! $this->puedeGestionarUsuario($usuario)) {
            abort(403, 'No tienes permiso para editar este usuario.');
        }

        $rolesGestionablesIds = $this->obtenerRolesGestionables()->pluck('id')->all();

        $validated = $request->validate([
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'run' => 'required|string|max:15|unique:users,run,'.$usuario->id,
            'correo_institucional' => 'required|email|max:150|unique:users,correo_institucional,'.$usuario->id,
            'cargo' => 'nullable|string|max:100',
            'departamento' => 'nullable|string|max:100',
            'rol_id' => ['required', 'exists:roles,id', Rule::in($rolesGestionablesIds)],
            'jefe_directo_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('rol_id', $this->obtenerRolDirectorId())),
            ],
        ]);

        $oldData = $usuario->toArray();

        try {
            $validated['run'] = $this->formatearRun($validated['run']);
        } catch (\Exception $e) {
            return back()->withErrors(['run' => 'El RUN ingresado no es valido.'])->withInput();
        }

        $this->validarUnicidadDirector((int) $validated['rol_id'], $usuario->id);

        $usuario->update($validated);

        AuditoriaHelper::registrar('users', $usuario->id, 'usuario_actualizado', Auth::user()->id, $oldData, $usuario->toArray());

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function toggle($id)
    {
        $usuario = User::findOrFail($id);

        if (! $this->puedeGestionarUsuario($usuario)) {
            abort(403, 'No tienes permiso para cambiar el estado de este usuario.');
        }

        $oldData = $usuario->toArray();
        $usuario->activo = ! $usuario->activo;
        $usuario->save();

        AuditoriaHelper::registrar(
            'users',
            $usuario->id,
            $usuario->activo ? 'usuario_activado' : 'usuario_desactivado',
            Auth::user()->id,
            $oldData,
            $usuario->toArray()
        );

        $mensaje = $usuario->activo ? 'Usuario habilitado nuevamente.' : 'Usuario deshabilitado correctamente.';

        return redirect()->route('admin.usuarios.index')->with('success', $mensaje);
    }

    public function resetPassword(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        if (! $this->puedeGestionarUsuario($usuario)) {
            abort(403, 'No tienes permiso para restablecer la contrasena de este usuario.');
        }

        $validated = $request->validate([
            'current_password' => ['required', 'current_password:web'],
            'password' => $this->passwordRules(),
        ], array_merge($this->passwordMessages(), [
            'current_password.required' => 'Debes ingresar tu contrasena actual.',
            'current_password.current_password' => 'La contrasena actual no coincide con tu cuenta.',
        ]));

        DB::transaction(function () use ($usuario, $validated): void {
            $oldData = ['password' => 'encrypted'];

            $usuario->password = Hash::make($validated['password']);
            $usuario->setRememberToken(Str::random(60));
            $usuario->save();

            $this->invalidarSesionesUsuario($usuario);

            AuditoriaHelper::registrar('users', $usuario->id, 'usuario_password_restablecida', Auth::user()->id, $oldData, ['password' => 'encrypted']);
        });

        return redirect()->route('admin.usuarios.index')->with('success', 'Contrasena restablecida correctamente.');
    }

    /**
     * @return array<int, mixed>
     */
    private function passwordRules(): array
    {
        return ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()];
    }

    /**
     * @return array<string, string>
     */
    private function passwordMessages(): array
    {
        return [
            'password.required' => 'Debes ingresar una contrasena.',
            'password.confirmed' => 'La confirmacion de la contrasena no coincide.',
            'password.min' => 'La contrasena debe tener al menos 8 caracteres.',
            'password.letters' => 'La contrasena debe incluir al menos una letra.',
            'password.numbers' => 'La contrasena debe incluir al menos un numero.',
        ];
    }

    private function invalidarSesionesUsuario(User $usuario): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        DB::connection(config('session.connection'))
            ->table((string) config('session.table', 'sessions'))
            ->where('user_id', $usuario->id)
            ->delete();
    }

    private function formatearRun(string $run): string
    {
        $run = strtoupper(trim($run));
        $run = str_replace(['.', '-'], '', $run);

        $numero = substr($run, 0, -1);
        $dv = substr($run, -1);

        if (! ctype_digit($numero)) {
            throw new \Exception('RUN invalido');
        }

        $suma = 0;
        $multiplo = 2;

        for ($i = strlen($numero) - 1; $i >= 0; $i--) {
            $suma += $numero[$i] * $multiplo;
            $multiplo = $multiplo == 7 ? 2 : $multiplo + 1;
        }

        $resto = $suma % 11;
        $dvEsperado = 11 - $resto;

        if ($dvEsperado == 11) {
            $dvEsperado = '0';
        } elseif ($dvEsperado == 10) {
            $dvEsperado = 'K';
        } else {
            $dvEsperado = (string) $dvEsperado;
        }

        if ($dv !== $dvEsperado) {
            throw new \Exception('RUN invalido');
        }

        return number_format($numero, 0, '', '.').'-'.$dv;
    }

    private function obtenerRolDirectorId(): int
    {
        return (int) Rol::where('nombre', 'jefe_directo')->value('id');
    }

    private function obtenerRolesGestionables()
    {
        $rolActual = strtolower(Auth::user()?->rol?->nombre ?? '');

        if ($rolActual === 'admin') {
            return Rol::orderBy('nombre')->get();
        }

        return Rol::whereIn('nombre', ['funcionario', 'jefe_directo', 'secretaria'])
            ->orderBy('nombre')
            ->get();
    }

    private function obtenerDirectoresActivos(?int $excluirUsuarioId = null)
    {
        $query = User::where('activo', true)
            ->where('rol_id', $this->obtenerRolDirectorId())
            ->orderBy('nombres');

        if ($excluirUsuarioId) {
            $query->where('id', '!=', $excluirUsuarioId);
        }

        return $query->get();
    }

    private function validarUnicidadDirector(int $rolId, ?int $usuarioActualId = null): void
    {
        $rolDirectorId = $this->obtenerRolDirectorId();

        if ($rolId !== $rolDirectorId) {
            return;
        }

        $query = User::where('rol_id', $rolDirectorId);

        if ($usuarioActualId) {
            $query->where('id', '!=', $usuarioActualId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'rol_id' => 'Ya existe un director registrado en el sistema.',
            ]);
        }
    }

    private function puedeGestionarUsuario(User $usuario): bool
    {
        $rolActual = strtolower(Auth::user()?->rol?->nombre ?? '');

        if ($rolActual === 'admin') {
            return true;
        }

        $rolesGestionablesIds = $this->obtenerRolesGestionables()->pluck('id')->all();

        return in_array((int) $usuario->rol_id, $rolesGestionablesIds, true);
    }
}
