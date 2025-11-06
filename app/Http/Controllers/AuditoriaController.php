<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;

class AuditoriaController extends Controller
{
    public function index()
    {
        $registros = Auditoria::with('usuario')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.auditoria.index', compact('registros'));
    }
}
