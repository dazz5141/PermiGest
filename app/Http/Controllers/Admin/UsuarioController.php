<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Rol;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    /**
     * Listado general de usuarios
     */
    public function index()
    {
        $usuarios = User::with('rol', 'jefeDirecto')->orderBy('nombres')->get();
        $roles = Rol::orderBy('nombre')->get();
        $jefes = User::where('activo', true)->orderBy('nombres')->get();

        return view('admin.usuarios.index', compact('usuarios', 'roles', 'jefes'));
    }

    /**
     * Crear nuevo usuario
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
            'jefe_directo_id' => 'nullable|exists:users,id',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['activo'] = true;

        User::create($validated);

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    /**
     * Formulario de edición
     */
    public function edit($id)
    {
        $usuario = User::findOrFail($id);
        $roles = Rol::orderBy('nombre')->get();
        $jefes = User::where('id', '!=', $usuario->id)->orderBy('nombres')->get();

        return view('admin.usuarios.edit', compact('usuario', 'roles', 'jefes'));
    }

    /**
     * Actualizar usuario
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
            'jefe_directo_id' => 'nullable|exists:users,id',
        ]);

        $usuario->update($validated);

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Activar / Desactivar usuario
     */
    public function toggle($id)
    {
        $usuario = User::findOrFail($id);
        $usuario->activo = !$usuario->activo;
        $usuario->save();

        $mensaje = $usuario->activo
            ? 'Usuario habilitado nuevamente.'
            : 'Usuario deshabilitado correctamente.';

        return redirect()->route('admin.usuarios.index')->with('success', $mensaje);
    }

    /**
     * Restablecer contraseña desde modal
     */
    public function resetPassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $usuario = User::findOrFail($id);
        $usuario->password = Hash::make($request->password);
        $usuario->save();

        return redirect()->route('admin.usuarios.index')->with('success', 'Contraseña restablecida correctamente.');
    }
}
