<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProgresoHistoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener usuarios clave
        $admin = \App\Models\Usuario::find(1);
        $user  = \App\Models\Usuario::find(2);

        // Obtener todos los niveles de historia ordenados
        $niveles = \App\Models\NivelesHistoria::orderBy('orden')->get();

        if ($niveles->isEmpty()) {
            $this->command->warn('No hay niveles de historia creados. Ejecuta primero NivelesHistoriaSeeder.');
            return;
        }

        // 1. Admin ha completado TODOS los niveles
        if ($admin) {
            foreach ($niveles as $nivel) {
                \App\Models\ProgresoHistoria::firstOrCreate([
                    'usuario_id' => $admin->id,
                    'nivel_id'   => $nivel->id,
                ], [
                    'completado' => false,
                    'codigo_solucion_usuario' => '// Solución Maestra generada en Seeder',
                    
                ]);
            }
        }

        // 2. Usuario normal ha completado solo el PRIMER nivel
        if ($user && $niveles->count() > 0) {
            $primerNivel = $niveles->first();
            \App\Models\ProgresoHistoria::firstOrCreate([
                'usuario_id' => $user->id,
                'nivel_id'   => $primerNivel->id,
            ], [
                'completado' => false,
                'codigo_solucion_usuario' => 'print("Hola Mundo")',
            ]);
        }
    }
}
