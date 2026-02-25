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
        Schema::table('usuarios', function (Blueprint $table) {
            // Renombrar racha_dias a streak para consistencia con el frontend si existe
            if (Schema::hasColumn('usuarios', 'racha_dias')) {
                $table->renameColumn('racha_dias', 'streak');
            } else if (!Schema::hasColumn('usuarios', 'streak')) {
                $table->integer('streak')->default(0)->after('exp_total');
            }
        });

        Schema::table('usuarios', function (Blueprint $table) {
            // Racha máxima alcanzada - Añadir después de streak (ya renombrado)
            if (!Schema::hasColumn('usuarios', 'max_streak')) {
                $table->integer('max_streak')->default(0)->after('streak');
            }
            
            // Campo para saber cuándo fue la última vez que completó un nivel
            if (!Schema::hasColumn('usuarios', 'ultimo_nivel_completado_at')) {
                $table->timestamp('ultimo_nivel_completado_at')->nullable()->after('ultima_conexion');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            if (Schema::hasColumn('usuarios', 'streak')) {
                $table->renameColumn('streak', 'racha_dias');
            }
            $table->dropColumn(['ultimo_nivel_completado_at', 'max_streak']);
        });
    }
};
