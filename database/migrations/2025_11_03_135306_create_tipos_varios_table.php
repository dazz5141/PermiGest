<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_varios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->string('descripcion', 255)->nullable();
            $table->timestamps();
        });

        // ⚙️ Ahora agregamos la relación en solicitudes
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->foreignId('tipo_vario_id')
                ->nullable()
                ->after('jornada')
                ->constrained('tipos_varios')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->dropForeign(['tipo_vario_id']);
            $table->dropColumn('tipo_vario_id');
        });

        Schema::dropIfExists('tipos_varios');
    }
};
