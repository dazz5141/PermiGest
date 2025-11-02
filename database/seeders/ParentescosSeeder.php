<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Parentesco;

class ParentescosSeeder extends Seeder
{
    public function run(): void
    {
        $parentescos = [
            ['nombre' => 'Hijo(a) / Cónyuge', 'observacion' => 'Hasta 7 días hábiles.'],
            ['nombre' => 'Padre / Madre', 'observacion' => 'Hasta 7 días hábiles.'],
            ['nombre' => 'Hermano(a)', 'observacion' => 'Hasta 3 días hábiles.'],
            ['nombre' => 'Otro familiar', 'observacion' => 'Hasta 1 día hábil.'],
        ];

        foreach ($parentescos as $p) {
            \App\Models\Parentesco::firstOrCreate(['nombre' => $p['nombre']], $p);
        }
    }
}
