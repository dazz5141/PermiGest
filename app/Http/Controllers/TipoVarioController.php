<?php

namespace App\Http\Controllers;

use App\Models\TipoVario;
use Illuminate\Http\Request;
use App\Helpers\AuditoriaHelper;
use Illuminate\Support\Facades\Auth;

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

        $tipo = TipoVario::create($request->only('nombre', 'descripcion'));

        /**
         * AUDITORÍA — creación
         */
        AuditoriaHelper::registrar(
            'tipos_varios',        
            $tipo->id,             
            'tipo_creado',   
            Auth::user()->id,       
            null,                  
            $tipo->toArray()       
        );

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

        // Guardamos datos anteriores
        $datosAntes = $tipo->toArray();

        // Actualizamos
        $tipo->update($request->only('nombre', 'descripcion'));

        /**
         * AUDITORÍA — actualización
         */
        AuditoriaHelper::registrar(
            'tipos_varios',
            $tipo->id,
            'tipo_actualizado', 
            Auth::user()->id,
            $datosAntes,          
            $tipo->toArray()      
        );

        return redirect()->route('tiposvarios.index')
            ->with('success', 'Tipo de permiso actualizado correctamente.');
    }

    /**
     * Eliminar tipo vario
     */
    public function destroy($id)
    {
        $tipo = TipoVario::findOrFail($id);

        // Datos antes de borrar
        $datosEliminados = $tipo->toArray();

        // Borrar
        $tipo->delete();

        /**
         * AUDITORÍA — eliminación
         */
        AuditoriaHelper::registrar(
            'tipos_varios',
            $id,
            'tipo_eliminado', 
            Auth::user()->id,
            $datosEliminados,    
            null                  
        );

        return back()->with('success', 'Tipo de permiso eliminado correctamente.');
    }
}
