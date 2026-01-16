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
        Schema::create('runs_roguelike', function (Blueprint $table) {
            $table->id();

            $table->foreignId('usuario_id')->constrained()->onDelete('cascade');
            $table->integer('vidas_restantes')->default(3);
            $table->integer('niveles_superados')->default(0);
            $table->integer('monedas_obtenidas')->default(0);
            $table->enum('estado', ['activo', 'completado', 'abandonado', 'fallido'])->default('activo');
            $table->json('data_partida')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('runs_roguelike');
    }
};
