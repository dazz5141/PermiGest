<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('tipo_solicitud_id')->constrained('tipos_solicitud');
            $table->foreignId('estado_solicitud_id')->default(1)->constrained('estados_solicitud');
            $table->foreignId('parentesco_id')->nullable()->constrained('parentescos')->nullOnDelete();

            $table->text('motivo')->nullable();
            $table->date('fecha_desde')->nullable();
            $table->date('fecha_hasta')->nullable();
            $table->time('hora_desde')->nullable();
            $table->time('hora_hasta')->nullable();
            $table->decimal('dias_solicitados', 4, 1)->nullable();
            $table->string('jornada', 20)->nullable();
            $table->text('observaciones')->nullable();

            // Aprobación
            $table->foreignId('validador_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_envio')->nullable();
            $table->timestamp('fecha_revision')->nullable();
            $table->text('observaciones_validador')->nullable();
            $table->boolean('firma_validador')->default(false);
            $table->string('token_validacion', 100)->unique()->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes');
    }
};
