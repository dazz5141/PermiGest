<?php

namespace App\Http\Controllers;

use App\Models\EstadoSolicitud;
use Illuminate\Http\Request;

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
     * Crear nuevo estado
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:50',
        ]);

        EstadoSolicitud::create($request->only('nombre'));

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

        $estado->update($request->only('nombre'));

        return back()->with('success', 'Estado actualizado correctamente.');
    }

    /**
     * Eliminar estado
     */
    public function destroy($id)
    {
        $estado = EstadoSolicitud::findOrFail($id);
        $estado->delete();

        return back()->with('success', 'Estado eliminado correctamente.');
    }
}
