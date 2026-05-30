<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rols') && DB::table('rols')->count() === 0) {
            Schema::drop('rols');
        }
    }

    public function down(): void
    {
        Schema::create('rols', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
};
