<?php

namespace App\Http\Controllers;

use App\Models\Parentesco;
use Illuminate\Http\Request;

class ParentescoController extends Controller
{
    /**
     * Listar todos los parentescos
     */
    public function index()
    {
        $parentescos = Parentesco::orderBy('id')->get();
        return view('admin.parentescos.index', compact('parentescos'));
    }

    /**
     * Crear un nuevo parentesco
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'observacion' => 'nullable|string|max:255',
        ]);

        Parentesco::create($request->only('nombre', 'observacion'));

        return back()->with('success', 'Parentesco agregado correctamente.');
    }

    /**
     * Actualizar parentesco existente
     */
    public function update(Request $request, $id)
    {
        $parentesco = Parentesco::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:100',
            'observacion' => 'nullable|string|max:255',
        ]);

        $parentesco->update($request->only('nombre', 'observacion'));

        return back()->with('success', 'Parentesco actualizado correctamente.');
    }

    /**
     * Eliminar parentesco
     */
    public function destroy($id)
    {
        $parentesco = Parentesco::findOrFail($id);
        $parentesco->delete();

        return back()->with('success', 'Parentesco eliminado correctamente.');
    }
}
