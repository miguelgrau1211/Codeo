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
        // Índices de rendimiento para las tablas más consultadas en el Dashboard
        
        Schema::table('usuarios', function (Blueprint $table) {
            $table->index('exp_total', 'idx_usuarios_exp');
            $table->index('es_admin', 'idx_usuarios_admin');
        });

        Schema::table('usuario_logros_', function (Blueprint $table) {
            $table->index('usuario_id', 'idx_logros_user');
        });

        Schema::table('usuario_progreso_historia', function (Blueprint $table) {
            $table->index(['usuario_id', 'completado'], 'idx_progreso_user_comp');
        });

        Schema::table('runs_roguelike', function (Blueprint $table) {
            $table->index('usuario_id', 'idx_roguelike_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropIndex('idx_usuarios_exp');
            $table->dropIndex('idx_usuarios_admin');
        });

        Schema::table('usuario_logros_', function (Blueprint $table) {
            $table->dropIndex('idx_logros_user');
        });

        Schema::table('usuario_progreso_historia', function (Blueprint $table) {
            $table->dropIndex('idx_progreso_user_comp');
        });

        Schema::table('runs_roguelike', function (Blueprint $table) {
            $table->dropIndex('idx_roguelike_user');
        });
    }
};
