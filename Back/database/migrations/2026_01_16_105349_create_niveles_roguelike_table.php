<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('niveles_roguelike', function (Blueprint $table) {
            $table->id();
            $table->enum('dificultad', ['fácil', 'medio', 'difícil', 'extremo']);
            $table->string("titulo");
            $table->text("descripcion");
            $table->text("codigo_validador");
            $table->integer("recompensa_monedas");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('niveles_roguelike');
    }
};
