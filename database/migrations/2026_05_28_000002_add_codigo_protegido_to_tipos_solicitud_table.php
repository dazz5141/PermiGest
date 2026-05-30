<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipos_solicitud', function (Blueprint $table) {
            $table->string('codigo', 50)->nullable()->after('id');
            $table->boolean('protegido')->default(false)->after('descripcion');
        });

        $tiposBase = [
            1 => ['nombre' => 'Con goce de sueldo', 'codigo' => 'con_goce'],
            2 => ['nombre' => 'Sin goce de sueldo', 'codigo' => 'sin_goce'],
            3 => ['nombre' => 'Permiso por defuncion', 'codigo' => 'defuncion'],
            4 => ['nombre' => 'Permisos varios', 'codigo' => 'varios'],
        ];

        foreach ($tiposBase as $id => $tipo) {
            DB::table('tipos_solicitud')
                ->where('id', $id)
                ->orWhere('nombre', $tipo['nombre'])
                ->update([
                    'codigo' => $tipo['codigo'],
                    'protegido' => true,
                ]);
        }

        DB::table('tipos_solicitud')
            ->whereNull('codigo')
            ->orderBy('id')
            ->get(['id', 'nombre'])
            ->each(function ($tipo) {
                $codigoBase = strtolower(trim((string) preg_replace('/[^A-Za-z0-9]+/', '_', $tipo->nombre), '_'));
                $codigo = $codigoBase !== '' ? $codigoBase : 'tipo_' . $tipo->id;
                $codigoFinal = $codigo;
                $contador = 2;

                while (DB::table('tipos_solicitud')->where('codigo', $codigoFinal)->exists()) {
                    $codigoFinal = $codigo . '_' . $contador;
                    $contador++;
                }

                DB::table('tipos_solicitud')
                    ->where('id', $tipo->id)
                    ->update(['codigo' => $codigoFinal]);
            });

        Schema::table('tipos_solicitud', function (Blueprint $table) {
            $table->unique('codigo');
        });
    }

    public function down(): void
    {
        Schema::table('tipos_solicitud', function (Blueprint $table) {
            $table->dropUnique(['codigo']);
            $table->dropColumn(['codigo', 'protegido']);
        });
    }
};
