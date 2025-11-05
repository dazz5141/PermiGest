<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rol;

class RolController extends Controller
{
    /**
     * Listar roles
     */
    public function index()
    {
        $roles = Rol::orderBy('nombre')->get();
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Guardar nuevo rol
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:50|unique:roles,nombre',
            'descripcion' => 'nullable|string|max:150',
        ]);

        Rol::create($validated);

        return redirect()->route('admin.roles.index')->with('success', 'Rol creado correctamente.');
    }

    /**
     * Editar rol
     */
    public function edit($id)
    {
        $rol = Rol::findOrFail($id);
        return view('admin.roles.edit', compact('rol'));
    }

    /**
     * Actualizar rol
     */
    public function update(Request $request, $id)
    {
        $rol = Rol::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:50|unique:roles,nombre,' . $rol->id,
            'descripcion' => 'nullable|string|max:150',
        ]);

        $rol->update($validated);

        return redirect()->route('admin.roles.index')->with('success', 'Rol actualizado correctamente.');
    }

    /**
     * Eliminar rol (opcional)
     */
    public function destroy($id)
    {
        $rol = Rol::findOrFail($id);
        $rol->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Rol eliminado correctamente.');
    }
}
