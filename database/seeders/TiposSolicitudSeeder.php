<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoSolicitud;

class TiposSolicitudSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            ['nombre' => 'Con goce de sueldo', 'descripcion' => 'Permiso administrativo con remuneración.'],
            ['nombre' => 'Sin goce de sueldo', 'descripcion' => 'Permiso administrativo sin remuneración.'],
            ['nombre' => 'Permiso por defunción', 'descripcion' => 'Por fallecimiento de familiar directo.'],
            ['nombre' => 'Permisos varios', 'descripcion' => 'Permisos especiales o situaciones excepcionales.'],
        ];

        foreach ($tipos as $tipo) {
            \App\Models\TipoSolicitud::firstOrCreate(['nombre' => $tipo['nombre']], $tipo);
        }
    }
}
