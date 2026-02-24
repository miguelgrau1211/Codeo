<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('temas', function (Blueprint $table) {
            $table->boolean('es_exclusivo')->default(false)->after('precio');
        });

        // Insertar los 4 nuevos temas exclusivos
        DB::table('temas')->insert([
            [
                'nombre' => 'Cyber Volcanic',
                'descripcion' => 'Un entorno hostil con lava fluyendo entre circuitos oscuros.',
                'precio' => 0,
                'es_exclusivo' => true,
                'css_variables' => json_encode([
                    '--primary-bg' => '#1a0505',
                    '--secondary-bg' => '#2d0a0a',
                    '--accent-color' => '#ff4500',
                    '--text-main' => '#ffffff',
                    '--text-muted' => '#a1a1aa',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Aurora Borealis',
                'descripcion' => 'La mística de las luces del norte integrada en tu código.',
                'precio' => 0,
                'es_exclusivo' => true,
                'css_variables' => json_encode([
                    '--primary-bg' => '#051622',
                    '--secondary-bg' => '#0d2d3e',
                    '--accent-color' => '#deb992',
                    '--text-main' => '#ffffff',
                    '--text-muted' => '#8ba0b0',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Gold Rush',
                'descripcion' => 'Elegancia pura con toques de oro para los programadores de élite.',
                'precio' => 0,
                'es_exclusivo' => true,
                'css_variables' => json_encode([
                    '--primary-bg' => '#000000',
                    '--secondary-bg' => '#111111',
                    '--accent-color' => '#ffd700',
                    '--text-main' => '#ffffff',
                    '--text-muted' => '#c0c0c0',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Void Master',
                'descripcion' => 'Para aquellos que dominan el vacío. Oscuridad total con sombras violetas.',
                'precio' => 0,
                'es_exclusivo' => true,
                'css_variables' => json_encode([
                    '--primary-bg' => '#020202',
                    '--secondary-bg' => '#0a0a0c',
                    '--accent-color' => '#8a2be2',
                    '--text-main' => '#f8f8f8',
                    '--text-muted' => '#4b0082',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('temas', function (Blueprint $table) {
            $table->dropColumn('es_exclusivo');
        });
        
        DB::table('temas')->whereIn('nombre', ['Cyber Volcanic', 'Aurora Borealis', 'Gold Rush', 'Void Master'])->delete();
    }
};
