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
        // Actualizar tabla niveles_historia
        Schema::table('niveles_historia', function (Blueprint $table) {
            $table->json('test_cases')->nullable()->after('descripcion'); 
            $table->dropColumn('solucion_esperada');
        });

        // Actualizar tabla niveles_roguelike
        Schema::table('niveles_roguelike', function (Blueprint $table) {
            $table->json('test_cases')->nullable()->after('descripcion');
            $table->dropColumn('codigo_validador');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('niveles_historia', function (Blueprint $table) {
            $table->dropColumn('test_cases');
            $table->text('solucion_esperada')->nullable();
        });

        Schema::table('niveles_roguelike', function (Blueprint $table) {
            $table->dropColumn('test_cases');
            $table->text('solucion_esperada')->nullable();
        });
    }
};
