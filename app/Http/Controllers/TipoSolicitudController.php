<?php

namespace App\Http\Controllers;

use App\Models\TipoSolicitud;
use Illuminate\Http\Request;
use App\Helpers\AuditoriaHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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

        $tipo = TipoSolicitud::create([
            'codigo' => $this->generarCodigo($request->nombre),
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'protegido' => false,
        ]);

        /**
         * AUDITORÍA — creación
         */
        AuditoriaHelper::registrar(
            'tipos_solicitud',
            $tipo->id,
            'tipo_solicitud_creado', 
            Auth::user()->id,
            null,
            $tipo->toArray()
        );

        return redirect()->route('tipos.index')->with('success', 'Tipo de solicitud creado correctamente.');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $tipo = TipoSolicitud::findOrFail($id);

        if ($tipo->protegido) {
            abort(403, 'Este tipo base no se puede editar.');
        }

        return view('admin.tipos_solicitud.edit', compact('tipo'));
    }

    /**
     * Actualizar un tipo existente
     */
    public function update(Request $request, $id)
    {
        $tipo = TipoSolicitud::findOrFail($id);

        if ($tipo->protegido) {
            abort(403, 'Este tipo base no se puede editar.');
        }

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
            'tipo_solicitud_actualizado', 
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

        if ($tipo->protegido) {
            abort(403, 'Este tipo base no se puede eliminar.');
        }

        // Datos antes de borrar
        $oldData = $tipo->toArray();

        $tipo->delete();

        /**
         * AUDITORÍA — eliminación
         */
        AuditoriaHelper::registrar(
            'tipos_solicitud',
            $id,
            'tipo_solicitud_eliminado', 
            Auth::user()->id,
            $oldData,
            null
        );

        return redirect()->route('tipos.index')->with('success', 'Tipo de solicitud eliminado correctamente.');
    }

    private function generarCodigo(string $nombre): string
    {
        $codigoBase = Str::slug($nombre, '_') ?: 'tipo';
        $codigo = $codigoBase;
        $contador = 2;

        while (TipoSolicitud::where('codigo', $codigo)->exists()) {
            $codigo = $codigoBase . '_' . $contador;
            $contador++;
        }

        return $codigo;
    }
}
