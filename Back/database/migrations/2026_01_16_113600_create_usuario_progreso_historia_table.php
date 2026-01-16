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
        Schema::create('usuario_progreso_historia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId("nivel_id")->constrained("niveles_historia")->onDelete("cascade");
            $table->boolean('completado')->default(false);
            $table->text('codigo_solucion_usuario')->nullable();

            //claves unicas porque un usuario solo puede hacer una entrada por nivel
            $table->unique(['usuario_id', 'nivel_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_progreso_historia');
    }
};
