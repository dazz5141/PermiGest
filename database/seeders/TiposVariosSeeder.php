<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoVario;

class TiposVariosSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            ['nombre' => 'Comisión de servicio', 'descripcion' => 'Actividades en representación del establecimiento'],
            ['nombre' => 'Capacitación o perfeccionamiento', 'descripcion' => 'Cursos, talleres o diplomados'],
            ['nombre' => 'Representación institucional', 'descripcion' => 'Eventos oficiales o académicos'],
            ['nombre' => 'Trámite personal', 'descripcion' => 'Gestiones personales autorizadas'],
            ['nombre' => 'Atención médica', 'descripcion' => 'Consultas o exámenes médicos personales'],
            ['nombre' => 'Otro', 'descripcion' => 'Cualquier otro motivo no contemplado'],
        ];

        foreach ($tipos as $tipo) {
            TipoVario::firstOrCreate(['nombre' => $tipo['nombre']], $tipo);
        }
    }
}
