<?php

namespace App\Http\Controllers;

use App\Models\Parentesco;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\AuditoriaHelper;

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
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $parentesco = Parentesco::findOrFail($id);
        return view('admin.parentescos.edit', compact('parentesco'));
    }

    /**
     * Crear un nuevo parentesco
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre'       => 'required|string|max:100',
            'observacion'  => 'nullable|string|max:255',
        ]);

        $nuevo = Parentesco::create($request->only('nombre', 'observacion'));

        /**
         * ✅ AUDITORÍA — creación
         */
        AuditoriaHelper::registrar(
            'parentescos',
            $nuevo->id,
            'crear',
            Auth::user()->id,
            null,                    // datos antes
            $nuevo->toArray()        // datos después
        );

        return back()->with('success', 'Parentesco agregado correctamente.');
    }

    /**
     * Actualizar parentesco existente
     */
    public function update(Request $request, $id)
    {
        $parentesco = Parentesco::findOrFail($id);

        $request->validate([
            'nombre'       => 'required|string|max:100',
            'observacion'  => 'nullable|string|max:255',
        ]);

        // Datos antes
        $oldData = $parentesco->toArray();

        // Actualizamos
        $parentesco->update($request->only('nombre', 'observacion'));

        /**
         * AUDITORÍA — actualización
         */
        AuditoriaHelper::registrar(
            'parentescos',
            $parentesco->id,
            'actualizar',
            Auth::user()->id,
            $oldData,
            $parentesco->toArray()
        );

        return redirect()->route('parentescos.index')->with('success', 'Parentesco actualizado correctamente.');
    }

    /**
     * Eliminar parentesco
     */
    public function destroy($id)
    {
        $parentesco = Parentesco::findOrFail($id);

        // Datos antes de eliminar
        $oldData = $parentesco->toArray();

        // Eliminamos
        $parentesco->delete();

        /**
         * AUDITORÍA — eliminación
         */
        AuditoriaHelper::registrar(
            'parentescos',
            $id,
            'eliminar',
            Auth::user()->id,
            $oldData,
            null
        );

        return back()->with('success', 'Parentesco eliminado correctamente.');
    }
}
