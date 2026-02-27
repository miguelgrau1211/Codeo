<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Importante si usas eventos

class DatabaseSeeder extends Seeder
{
    /**
     * Ejecuta el seeder de la base de datos.
     */
    public function run(): void
    {
        // 1. Crear un usuario "admin" o de prueba conocido para poder loguearse
        Usuario::firstOrCreate(
            ['email' => 'admin@codeo.com'], // Buscamos por email para no duplicar
            [
                'nickname' => 'AdminCodeo',
                'nombre' => 'Administrador',
                'apellidos' => 'Sistema',
                'password' => Hash::make('12341234'), // Contraseña fácil para pruebas
                'es_admin' => true, // <--- ESTO ES LO IMPORTANTE
                'avatar_url' => 'https://ui-avatars.com/api/?name=Admin',
                'monedas' => 9999,
                'nivel_global' => 100,
                'exp_total' => 99999,
                'terminos_aceptados' => true
            ]
        );

        // 2. Crear 10 usuarios aleatorios (Solo en local/test)
        if (app()->environment('local', 'testing')) {
            Usuario::factory(10)->create();
        }

        // 3. Llamar a los otros seeders
        $this->call([
            TemaSeeder::class,
            LogrosSeeder::class,
            NivelesHistoriaSeeder::class,
            NivelRoguelikeSeeder::class,
            MejorasSeeder::class,
        ]);
    }
}
