<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rol;
use App\Helpers\AuditoriaHelper;
use Illuminate\Support\Facades\Auth;

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

        $nuevo = Rol::create($validated);

        /** AUDITORÍA — creación */
        AuditoriaHelper::registrar(
            'roles',             // tabla
            $nuevo->id,          // registro afectado
            'rol_creado',        // acción 
            Auth::user()->id,    // usuario ejecutor
            null,                // oldData
            $nuevo->toArray()    // newData
        );

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

        $oldData = $rol->toArray();

        $rol->update($validated);

        /** AUDITORÍA — actualización */
        AuditoriaHelper::registrar(
            'roles',
            $rol->id,
            'rol_actualizado',    
            Auth::user()->id,
            $oldData,
            $rol->toArray()
        );

        return redirect()->route('admin.roles.index')->with('success', 'Rol actualizado correctamente.');
    }

    /**
     * Eliminar rol (opcional)
     */
    public function destroy($id)
    {
        $rol = Rol::findOrFail($id);

        $oldData = $rol->toArray();

        $rol->delete();

        /** AUDITORÍA — eliminación */
        AuditoriaHelper::registrar(
            'roles',
            $id,
            'rol_eliminado',      
            Auth::user()->id,
            $oldData,
            null
        );

        return redirect()->route('admin.roles.index')->with('success', 'Rol eliminado correctamente.');
    }
}
