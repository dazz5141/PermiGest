<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PeriodoAdministrativo;
use Carbon\Carbon;

class PeriodoAdministrativoSeeder extends Seeder
{
    public function run(): void
    {
        $anio = now()->year;

        // Desactivar cualquier período activo previo (seguridad)
        PeriodoAdministrativo::where('activo', true)->update(['activo' => false]);

        // Crear período del año actual si no existe
        PeriodoAdministrativo::firstOrCreate(
            ['anio' => $anio],
            [
                'fecha_inicio'  => Carbon::create($anio, 1, 1),
                'fecha_termino' => Carbon::create($anio, 12, 31),
                'activo'        => true,
            ]
        );

        // Asegurar que el período actual quede activo
        PeriodoAdministrativo::where('anio', $anio)->update(['activo' => true]);
    }
}
