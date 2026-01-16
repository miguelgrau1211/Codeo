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
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id(); // ID autoincremental
            
            // Datos de autenticación y perfil
            $table->string('nickname', 50)->unique();
            $table->string('nombre')->nullable();
            $table->string('apellidos')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('avatar_url')->nullable();
            
            // Gamificación y Progreso
            $table->integer('monedas')->default(0);
            $table->integer('nivel_global')->default(1);
            $table->integer('exp_total')->default(0);
            $table->integer('racha_dias')->default(0);
            $table->timestamp('ultima_conexion')->nullable();
            
            // Configuración y legal
            $table->json('preferencias')->nullable(); // Para temas, sonidos, etc.
            $table->boolean('terminos_aceptados')->default(false);
            
            $table->rememberToken(); // Requerido para sesiones persistentes
            $table->timestamps(); // Crea created_at y updated_at automáticamente
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};