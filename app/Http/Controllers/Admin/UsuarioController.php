<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AuditoriaHelper;
use App\Http\Controllers\Controller;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UsuarioController extends Controller
{
    /**
     * Listado general de usuarios.
     */
    public function index()
    {
        $usuarios = User::with('rol', 'jefeDirecto')->orderBy('nombres')->get();
        $roles = Rol::orderBy('nombre')->get();
        $jefes = $this->obtenerDirectoresActivos();

        return view('admin.usuarios.index', compact('usuarios', 'roles', 'jefes'));
    }

    /**
     * Crear nuevo usuario.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'run' => 'required|string|max:15|unique:users,run',
            'correo_institucional' => 'required|email|max:150|unique:users,correo_institucional',
            'cargo' => 'nullable|string|max:100',
            'departamento' => 'nullable|string|max:100',
            'rol_id' => 'required|exists:roles,id',
            'jefe_directo_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('rol_id', $this->obtenerRolDirectorId())),
            ],
            'password' => 'required|string|min:6|confirmed',
        ]);

        try {
            $validated['run'] = $this->formatearRun($validated['run']);
        } catch (\Exception $e) {
            return back()
                ->withErrors(['run' => 'El RUN ingresado no es válido.'])
                ->withInput();
        }

        $this->validarUnicidadDirector((int) $validated['rol_id']);

        $validated['password'] = Hash::make($validated['password']);
        $validated['activo'] = true;

        $nuevo = User::create($validated);

        AuditoriaHelper::registrar(
            'users',
            $nuevo->id,
            'usuario_creado',
            Auth::id(),
            null,
            $nuevo->toArray()
        );

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    /**
     * Formulario de edición.
     */
    public function edit($id)
    {
        $usuario = User::findOrFail($id);
        $roles = Rol::orderBy('nombre')->get();
        $jefes = $this->obtenerDirectoresActivos($usuario->id);

        return view('admin.usuarios.edit', compact('usuario', 'roles', 'jefes'));
    }

    /**
     * Actualizar usuario.
     */
    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        $validated = $request->validate([
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'run' => 'required|string|max:15|unique:users,run,' . $usuario->id,
            'correo_institucional' => 'required|email|max:150|unique:users,correo_institucional,' . $usuario->id,
            'cargo' => 'nullable|string|max:100',
            'departamento' => 'nullable|string|max:100',
            'rol_id' => 'required|exists:roles,id',
            'jefe_directo_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('rol_id', $this->obtenerRolDirectorId())),
            ],
        ]);

        $oldData = $usuario->toArray();

        try {
            $validated['run'] = $this->formatearRun($validated['run']);
        } catch (\Exception $e) {
            return back()
                ->withErrors(['run' => 'El RUN ingresado no es válido.'])
                ->withInput();
        }

        $this->validarUnicidadDirector((int) $validated['rol_id'], $usuario->id);

        $usuario->update($validated);

        AuditoriaHelper::registrar(
            'users',
            $usuario->id,
            'usuario_actualizado',
            Auth::id(),
            $oldData,
            $usuario->toArray()
        );

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Activar / desactivar usuario.
     */
    public function toggle($id)
    {
        $usuario = User::findOrFail($id);

        $oldData = $usuario->toArray();
        $usuario->activo = !$usuario->activo;
        $usuario->save();

        AuditoriaHelper::registrar(
            'users',
            $usuario->id,
            $usuario->activo ? 'usuario_activado' : 'usuario_desactivado',
            Auth::id(),
            $oldData,
            $usuario->toArray()
        );

        $mensaje = $usuario->activo
            ? 'Usuario habilitado nuevamente.'
            : 'Usuario deshabilitado correctamente.';

        return redirect()->route('admin.usuarios.index')->with('success', $mensaje);
    }

    /**
     * Restablecer contraseña desde modal.
     */
    public function resetPassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $usuario = User::findOrFail($id);
        $oldData = ['password' => 'encrypted'];

        $usuario->password = Hash::make($request->password);
        $usuario->save();

        AuditoriaHelper::registrar(
            'users',
            $usuario->id,
            'usuario_password_restablecida',
            Auth::id(),
            $oldData,
            ['password' => 'encrypted']
        );

        return redirect()->route('admin.usuarios.index')->with('success', 'Contraseña restablecida correctamente.');
    }

    /**
     * Valida y formatea RUN chileno a 12.345.678-9.
     */
    private function formatearRun(string $run): string
    {
        $run = strtoupper(trim($run));
        $run = str_replace(['.', '-'], '', $run);

        $numero = substr($run, 0, -1);
        $dv = substr($run, -1);

        if (!ctype_digit($numero)) {
            throw new \Exception('RUN inválido');
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
            throw new \Exception('RUN inválido');
        }

        return number_format($numero, 0, '', '.') . '-' . $dv;
    }

    private function obtenerRolDirectorId(): int
    {
        return (int) Rol::where('nombre', 'jefe_directo')->value('id');
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
}
