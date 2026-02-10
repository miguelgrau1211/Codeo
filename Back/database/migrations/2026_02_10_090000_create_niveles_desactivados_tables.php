<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tabla para niveles de historia desactivados
        Schema::create('niveles_historia_desactivados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nivel_id_original'); // ID original para posible restauración
            $table->integer('orden'); // Guardamos el orden, pero sin unique por si acaso
            $table->string('titulo');
            $table->string('descripcion');
            $table->text('contenido_teorico');
            $table->text('codigo_inicial');
            $table->json('test_cases')->nullable();
            $table->integer('recompensa_exp')->default(100);
            $table->integer('recompensa_monedas')->default(50);
            $table->text('motivo')->nullable(); // Razón de desactivación
            $table->timestamp('fecha_desactivacion')->useCurrent();
            $table->timestamps();
        });

        // Tabla para niveles roguelike desactivados
        Schema::create('niveles_roguelike_desactivados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nivel_id_original');
            $table->enum('dificultad', ['fácil', 'medio', 'difícil', 'extremo']);
            $table->string("titulo");
            $table->text("descripcion");
            $table->json('test_cases')->nullable();
            $table->integer("recompensa_monedas");
            $table->text('motivo')->nullable();
            $table->timestamp('fecha_desactivacion')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('niveles_historia_desactivados');
        Schema::dropIfExists('niveles_roguelike_desactivados');
    }
};
