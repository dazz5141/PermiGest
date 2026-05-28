<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estados_solicitud', function (Blueprint $table) {
            $table->string('codigo', 50)->nullable()->after('id');
            $table->boolean('protegido')->default(false)->after('nombre');
        });

        $estadosBase = [
            'Pendiente' => 'pendiente',
            'Aprobado' => 'aprobado',
            'Rechazado' => 'rechazado',
            'Anulado' => 'anulado',
        ];

        foreach ($estadosBase as $nombre => $codigo) {
            DB::table('estados_solicitud')
                ->where('nombre', $nombre)
                ->update([
                    'codigo' => $codigo,
                    'protegido' => true,
                ]);
        }

        DB::table('estados_solicitud')
            ->whereNull('codigo')
            ->orderBy('id')
            ->get(['id', 'nombre'])
            ->each(function ($estado) {
                $codigoBase = strtolower(trim((string) preg_replace('/[^A-Za-z0-9]+/', '_', $estado->nombre), '_'));
                $codigo = $codigoBase !== '' ? $codigoBase : 'estado_' . $estado->id;
                $codigoFinal = $codigo;
                $contador = 2;

                while (DB::table('estados_solicitud')->where('codigo', $codigoFinal)->exists()) {
                    $codigoFinal = $codigo . '_' . $contador;
                    $contador++;
                }

                DB::table('estados_solicitud')
                    ->where('id', $estado->id)
                    ->update(['codigo' => $codigoFinal]);
            });

        Schema::table('estados_solicitud', function (Blueprint $table) {
            $table->unique('codigo');
        });
    }

    public function down(): void
    {
        Schema::table('estados_solicitud', function (Blueprint $table) {
            $table->dropUnique(['codigo']);
            $table->dropColumn(['codigo', 'protegido']);
        });
    }
};
