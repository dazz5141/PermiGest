<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('run', 15)->unique();
            $table->string('correo_institucional', 150)->unique();
            $table->string('cargo', 100)->nullable();
            $table->string('departamento', 100)->nullable();
            $table->string('password');
            $table->boolean('activo')->default(true);

            // Relaciones
            $table->foreignId('rol_id')->constrained('roles')->onDelete('restrict');
            $table->foreignId('jefe_directo_id')->nullable()->constrained('users')->nullOnDelete();

            // Manejo de sesiones de autenticación
            $table->rememberToken();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
