<?php

namespace App\Http\Controllers;

use App\Models\TipoVario;
use Illuminate\Http\Request;

class TipoVarioController extends Controller
{
    /**
     * Listar todos los tipos varios
     */
    public function index()
    {
        $tipos = TipoVario::orderBy('id')->get();
        return view('admin.tipos_varios.index', compact('tipos'));
    }

    /**
     * Guardar un nuevo tipo vario
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
        ]);

        TipoVario::create($request->only('nombre', 'descripcion'));

        return back()->with('success', 'Tipo de permiso agregado correctamente.');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $tipo = TipoVario::findOrFail($id);
        return view('admin.tipos_varios.edit', compact('tipo'));
    }

    /**
     * Actualizar tipo vario existente
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $tipo = TipoVario::findOrFail($id);
        $tipo->update($request->only('nombre', 'descripcion'));

        return redirect()->route('tiposvarios.index')->with('success', 'Tipo de permiso actualizado correctamente.');
    }

    /**
     * Eliminar tipo vario
     */
    public function destroy($id)
    {
        $tipo = TipoVario::findOrFail($id);
        $tipo->delete();

        return back()->with('success', 'Tipo de permiso eliminado correctamente.');
    }
}
