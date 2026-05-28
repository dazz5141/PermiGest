<?php

namespace App\Http\Controllers;

use App\Models\EstadoSolicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\AuditoriaHelper;
use Illuminate\Support\Str;

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

        if ($estado->protegido) {
            abort(403, 'Este estado base no se puede editar.');
        }

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

        $nuevo = EstadoSolicitud::create([
            'nombre' => $request->nombre,
            'codigo' => $this->generarCodigo($request->nombre),
            'protegido' => false,
        ]);

        /**
         * AUDITORÍA — creación
         */
        AuditoriaHelper::registrar(
            'estados_solicitud',   // tabla
            $nuevo->id,            // ID afectado
            'estado_solicitud_creado',   // acción
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

        if ($estado->protegido) {
            abort(403, 'Este estado base no se puede editar.');
        }

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
            'estado_solicitud_actualizado',  
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

        if ($estado->protegido) {
            abort(403, 'Este estado base no se puede eliminar.');
        }

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
            'estado_solicitud_eliminado',  
            Auth::user()->id,
            $oldData,
            null
        );

        return back()->with('success', 'Estado eliminado correctamente.');
    }

    private function generarCodigo(string $nombre): string
    {
        $codigoBase = Str::slug($nombre, '_') ?: 'estado';
        $codigo = $codigoBase;
        $contador = 2;

        while (EstadoSolicitud::where('codigo', $codigo)->exists()) {
            $codigo = $codigoBase . '_' . $contador;
            $contador++;
        }

        return $codigo;
    }
}
