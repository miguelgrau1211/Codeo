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
        Schema::create('reportes', function (Blueprint $table) {
            $table->id();
            // Relación con usuarios (si se borra usuario, se borran sus reportes)
            // Usamos 'usuarios' 
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->string("titulo");
            $table->text("descripcion");
            $table->enum("estado", ["pendiente", "en revision", "solucionado"])->default('pendiente');;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reportes');
    }
};
