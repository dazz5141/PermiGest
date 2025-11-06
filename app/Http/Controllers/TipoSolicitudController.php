<?php

namespace App\Http\Controllers;

use App\Models\TipoSolicitud;
use Illuminate\Http\Request;
use App\Helpers\AuditoriaHelper;
use Illuminate\Support\Facades\Auth;

class TipoSolicitudController extends Controller
{
    /**
     * Mostrar todos los tipos de solicitud
     */
    public function index()
    {
        $tipos = TipoSolicitud::orderBy('id')->get();
        return view('admin.tipos_solicitud.index', compact('tipos'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        return view('admin.tipos_solicitud.create');
    }

    /**
     * Guardar nuevo tipo de solicitud
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $tipo = TipoSolicitud::create($request->only('nombre', 'descripcion'));

        /**
         * AUDITORÍA — creación
         */
        AuditoriaHelper::registrar(
            'tipos_solicitud',     // tabla
            $tipo->id,            // id afectado
            'crear',              // acción
            Auth::user()->id,     // usuario
            null,                 // datos anteriores
            $tipo->toArray()      // datos nuevos
        );

        return redirect()->route('tipos.index')->with('success', 'Tipo de solicitud creado correctamente.');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $tipo = TipoSolicitud::findOrFail($id);
        return view('admin.tipos_solicitud.edit', compact('tipo'));
    }

    /**
     * Actualizar un tipo existente
     */
    public function update(Request $request, $id)
    {
        $tipo = TipoSolicitud::findOrFail($id);

        // Guardar datos anteriores
        $oldData = $tipo->toArray();

        $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
        ]);

        // Actualizar
        $tipo->update($request->only('nombre', 'descripcion'));

        /**
         * AUDITORÍA — actualización
         */
        AuditoriaHelper::registrar(
            'tipos_solicitud',
            $tipo->id,
            'actualizar',
            Auth::user()->id,
            $oldData,
            $tipo->toArray()
        );

        return redirect()->route('tipos.index')->with('success', 'Tipo de solicitud actualizado correctamente.');
    }

    /**
     * Eliminar tipo de solicitud
     */
    public function destroy($id)
    {
        $tipo = TipoSolicitud::findOrFail($id);

        // Datos antes de borrar
        $oldData = $tipo->toArray();

        $tipo->delete();

        /**
         * AUDITORÍA — eliminación
         */
        AuditoriaHelper::registrar(
            'tipos_solicitud',
            $id,
            'eliminar',
            Auth::user()->id,
            $oldData,
            null
        );

        return redirect()->route('tipos.index')->with('success', 'Tipo de solicitud eliminado correctamente.');
    }
}
