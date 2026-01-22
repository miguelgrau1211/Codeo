<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Usuario>
 */
class UsuarioFactory extends Factory
{
    protected $model = \App\Models\Usuario::class;

    public function definition(): array
    {
        return [
            'nickname' => fake()->unique()->userName(),
            'nombre' => fake()->firstName(),
            'apellidos' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'), 
            'avatar_url' => 'https://api.dicebear.com/7.x/pixel-art/svg?seed=' . Str::random(5),
            'monedas' => fake()->numberBetween(0, 500),
            'nivel_global' => fake()->numberBetween(1, 20),
            'exp_total' => fake()->numberBetween(0, 5000),
            'racha_dias' => fake()->numberBetween(0, 10),
            'ultima_conexion' => fake()->dateTimeBetween('-1 month', 'now'),
            'preferencias' => [],
            'terminos_aceptados' => true,
        ];
    }
}
