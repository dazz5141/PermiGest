<?php

namespace App\Http\Controllers;

use App\Models\EstadoSolicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\AuditoriaHelper;

class EstadoSolicitudController extends Controller
{
    /**
     * Listar estados disponibles
     */
    public function index()
    {
        $estados = EstadoSolicitud::orderBy('id')->get();
        return view('admin.estados_solicitud.index', compact('estados'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $estado = EstadoSolicitud::findOrFail($id);
        return view('admin.estados_solicitud.edit', compact('estado'));
    }

    /**
     * Crear nuevo estado
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:50',
        ]);

        $nuevo = EstadoSolicitud::create($request->only('nombre'));

        /**
         * AUDITORÍA — creación
         */
        AuditoriaHelper::registrar(
            'estados_solicitud',   // tabla
            $nuevo->id,            // ID afectado
            'crear',               // acción
            Auth::user()->id,      // usuario
            null,                  // old values
            $nuevo->toArray()      // new values
        );

        return back()->with('success', 'Estado creado correctamente.');
    }

    /**
     * Actualizar estado existente
     */
    public function update(Request $request, $id)
    {
        $estado = EstadoSolicitud::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:50',
        ]);

        // datos antes
        $oldData = $estado->toArray();

        // actualización
        $estado->update($request->only('nombre'));

        /**
         * AUDITORÍA — actualización
         */
        AuditoriaHelper::registrar(
            'estados_solicitud',
            $estado->id,
            'actualizar',
            Auth::user()->id,
            $oldData,
            $estado->toArray()
        );

        return redirect()->route('estados.index')->with('success', 'Estado actualizado correctamente.');
    }

    /**
     * Eliminar estado
     */
    public function destroy($id)
    {
        $estado = EstadoSolicitud::findOrFail($id);

        // datos antes de eliminar
        $oldData = $estado->toArray();

        // eliminar
        $estado->delete();

        /**
         * AUDITORÍA — eliminación
         */
        AuditoriaHelper::registrar(
            'estados_solicitud',
            $id,
            'eliminar',
            Auth::user()->id,
            $oldData,
            null
        );

        return back()->with('success', 'Estado eliminado correctamente.');
    }
}
