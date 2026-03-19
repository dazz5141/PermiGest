<?php

namespace Database\Seeders;

use App\Models\EstadoSolicitud;
use Illuminate\Database\Seeder;

class EstadosSolicitudSeeder extends Seeder
{
    public function run(): void
    {
        $estados = [
            ['nombre' => 'Pendiente'],
            ['nombre' => 'Aprobado'],
            ['nombre' => 'Rechazado'],
            ['nombre' => 'Anulado'],
        ];

        foreach ($estados as $estado) {
            EstadoSolicitud::firstOrCreate(['nombre' => $estado['nombre']]);
        }
    }
}
