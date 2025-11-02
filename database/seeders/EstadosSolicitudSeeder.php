<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EstadoSolicitud;

class EstadosSolicitudSeeder extends Seeder
{
    public function run(): void
    {
        $estados = [
            ['nombre' => 'Pendiente'],
            ['nombre' => 'En revisión'],
            ['nombre' => 'Aprobado'],
            ['nombre' => 'Rechazado'],
            ['nombre' => 'Anulado'],
        ];

        foreach ($estados as $estado) {
            \App\Models\EstadoSolicitud::firstOrCreate(['nombre' => $estado['nombre']]);
        }
    }
}
