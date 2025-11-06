<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditorias', function (Blueprint $table) {
            $table->id();

            // Usuario que realizó la acción
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Tabla afectada
            $table->string('tabla', 100);

            // ID del registro afectado
            $table->unsignedBigInteger('registro_id')->nullable();

            // Tipo de acción: create, update, delete, login, etc.
            $table->string('accion', 50);

            // Antes y después (JSON)
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();

            // Información opcional del navegador y IP
            $table->string('ip', 45)->nullable();
            $table->string('navegador', 255)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditorias');
    }
};
