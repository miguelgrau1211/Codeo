<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Importante si usas eventos

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
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
                'avatar_url' => 'https://ui-avatars.com/api/?name=Admin',
                'monedas' => 9999,
                'nivel_global' => 100,
                'exp_total' => 99999,
                'terminos_aceptados' => true
            ]
        );

        // 2. Crear 10 usuarios aleatorios
        Usuario::factory(10)->create();

        // 3. Llamar a los otros seeders
        $this->call([
            LogrosSeeder::class,
            NivelesHistoriaSeeder::class,
            NivelRoguelikeSeeder::class,
            MejorasSeeder::class,
        ]);
    }
}
