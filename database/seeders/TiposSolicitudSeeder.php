<?php

namespace Database\Seeders;

use App\Models\TipoSolicitud;
use Illuminate\Database\Seeder;

class TiposSolicitudSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            [
                'codigo' => TipoSolicitud::CODIGO_CON_GOCE,
                'nombre' => 'Con goce de sueldo',
                'descripcion' => 'Permiso administrativo con remuneracion.',
                'protegido' => true,
            ],
            [
                'codigo' => TipoSolicitud::CODIGO_SIN_GOCE,
                'nombre' => 'Sin goce de sueldo',
                'descripcion' => 'Permiso administrativo sin remuneracion.',
                'protegido' => true,
            ],
            [
                'codigo' => TipoSolicitud::CODIGO_DEFUNCION,
                'nombre' => 'Permiso por defuncion',
                'descripcion' => 'Por fallecimiento de familiar directo.',
                'protegido' => true,
            ],
            [
                'codigo' => TipoSolicitud::CODIGO_VARIOS,
                'nombre' => 'Permisos varios',
                'descripcion' => 'Permisos especiales o situaciones excepcionales.',
                'protegido' => true,
            ],
        ];

        foreach ($tipos as $tipo) {
            TipoSolicitud::updateOrCreate(['codigo' => $tipo['codigo']], $tipo);
        }
    }
}
