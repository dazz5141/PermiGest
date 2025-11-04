<?php

namespace App\Http\Controllers;

use App\Models\TipoSolicitud;
use Illuminate\Http\Request;

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
     * Guardar nuevo tipo
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
        ]);

        TipoSolicitud::create($request->only('nombre', 'descripcion'));

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
     * Actualizar tipo existente
     */
    public function update(Request $request, $id)
    {
        $tipo = TipoSolicitud::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $tipo->update($request->only('nombre', 'descripcion'));

        return redirect()->route('tipos.index')->with('success', 'Tipo de solicitud actualizado correctamente.');
    }

    /**
     * Eliminar tipo
     */
    public function destroy($id)
    {
        $tipo = TipoSolicitud::findOrFail($id);
        $tipo->delete();

        return redirect()->route('tipos.index')->with('success', 'Tipo de solicitud eliminado correctamente.');
    }
}
