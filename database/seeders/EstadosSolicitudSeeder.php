<?php

namespace Database\Seeders;

use App\Models\EstadoSolicitud;
use Illuminate\Database\Seeder;

class EstadosSolicitudSeeder extends Seeder
{
    public function run(): void
    {
        $estados = [
            ['codigo' => 'pendiente', 'nombre' => 'Pendiente', 'protegido' => true],
            ['codigo' => 'aprobado', 'nombre' => 'Aprobado', 'protegido' => true],
            ['codigo' => 'rechazado', 'nombre' => 'Rechazado', 'protegido' => true],
            ['codigo' => 'anulado', 'nombre' => 'Anulado', 'protegido' => true],
        ];

        foreach ($estados as $estado) {
            EstadoSolicitud::updateOrCreate(['codigo' => $estado['codigo']], $estado);
        }
    }
}
