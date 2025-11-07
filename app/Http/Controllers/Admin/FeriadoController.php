<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Feriado;
use App\Helpers\AuditoriaHelper;
use Illuminate\Support\Facades\Auth;

class FeriadoController extends Controller
{
    /** Listar todos los feriados */
    public function index()
    {
        $feriados = Feriado::orderBy('fecha')->get();
        return view('admin.feriados.index', compact('feriados'));
    }

    /** Formulario de creación */
    public function create()
    {
        return view('admin.feriados.create');
    }

    /** Guardar nuevo feriado */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha' => 'required|date|unique:feriados,fecha',
            'nombre' => 'required|string|max:150',
            'tipo' => 'nullable|string|max:100',
        ]);

        $nuevo = Feriado::create($validated);

        AuditoriaHelper::registrar(
            'feriados',
            $nuevo->id,
            'feriado_creado',
            Auth::user()->id,
            null,
            $nuevo->toArray()
        );

        return redirect()->route('admin.feriados.index')
            ->with('success', 'Feriado creado correctamente.');
    }

    /** Formulario de edición */
    public function edit($id)
    {
        $feriado = Feriado::findOrFail($id);
        return view('admin.feriados.edit', compact('feriado'));
    }

    /** Actualizar feriado existente */
    public function update(Request $request, $id)
    {
        $feriado = Feriado::findOrFail($id);

        $validated = $request->validate([
            'fecha' => 'required|date|unique:feriados,fecha,' . $id,
            'nombre' => 'required|string|max:150',
            'tipo' => 'nullable|string|max:100',
        ]);

        $oldData = $feriado->toArray();

        $feriado->update($validated);

        AuditoriaHelper::registrar(
            'feriados',
            $feriado->id,
            'feriado_actualizado',
            Auth::user()->id,
            $oldData,
            $feriado->toArray()
        );

        return redirect()->route('admin.feriados.index')
            ->with('success', 'Feriado actualizado correctamente.');
    }

    /** Eliminar feriado */
    public function destroy($id)
    {
        $feriado = Feriado::findOrFail($id);
        $oldData = $feriado->toArray();

        $feriado->delete();

        AuditoriaHelper::registrar(
            'feriados',
            $id,
            'feriado_eliminado',
            Auth::user()->id,
            $oldData,
            null
        );

        return redirect()->route('admin.feriados.index')
            ->with('success', 'Feriado eliminado correctamente.');
    }
}
