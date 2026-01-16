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
        Schema::create('niveles_historia', function (Blueprint $table) {
            $table->id();
            $table->integer('orden')->unique();
            $table->string('titulo');
            $table->string('descripcion');
            $table->text('contenido_teorico');
            $table->text('codigo_inicial');
            $table->text('solucion_esperada');
            $table->integer('recompensa_exp')->default(100);
            $table->integer('recompensa_monedas')->default(50);

            $table->timestamps();
            

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('niveles_historia');
    }
};
