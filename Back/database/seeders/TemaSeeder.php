<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tema;

class TemaSeeder extends Seeder
{
    public function run(): void
    {
        $temas = [
            [
                'nombre' => 'Deep Space',
                'descripcion' => 'Tema oficial con motor de fondo animado. Inmersión total en el código.',
                'precio' => 0,
                'css_variables' => [
                    '--primary-bg' => '#050a14',
                    '--secondary-bg' => '#0a1020',
                    '--accent-color' => '#8b5cf6',
                    '--text-main' => '#e2e8f0',
                    '--text-muted' => '#64748b',
                    '--editor-bg-img' => '/modo-historia/fondo-alternativo.png',
                ],
                'preview_img' => 'assets/themes/deep-space.png',
            ],
            [
                'nombre' => 'Default Dark',
                'descripcion' => 'El tema oscuro clásico de Codeo (Sin animaciones).',
                'precio' => 0,
                'css_variables' => [
                    '--primary-bg' => '#0f172a',
                    '--secondary-bg' => '#1e293b',
                    '--accent-color' => '#3b82f6',
                    '--text-main' => '#f8fafc',
                    '--text-muted' => '#94a3b8',
                ],
                'preview_img' => 'assets/themes/default-dark.png',
            ],
            [
                'nombre' => 'Daylight',
                'descripcion' => 'Modo claro oficial. Suavizado para no cansar la vista.',
                'precio' => 0,
                'css_variables' => [
                    '--primary-bg' => '#e2e8f0', // Soft gray-blue instead of white
                    '--secondary-bg' => '#f1f5f9', // Very soft gray
                    '--accent-color' => '#2563eb',
                    '--text-main' => '#1e293b',
                    '--text-muted' => '#475569',
                ],
                'preview_img' => 'assets/themes/light-mode.png',
            ],
            [
                'nombre' => 'Neon Cyberpunk',
                'descripcion' => 'Colores vibrantes y futuristas.',
                'precio' => 500,
                'css_variables' => [
                    '--primary-bg' => '#050505',
                    '--secondary-bg' => '#1a1a2e',
                    '--accent-color' => '#f013bd',
                    '--text-main' => '#00fff5',
                    '--text-muted' => '#700fb0',
                ],
                'preview_img' => 'assets/themes/neon-cyber.png',
            ],
            [
                'nombre' => 'Forest Zen',
                'descripcion' => 'Tranquilidad y naturaleza para programar en paz.',
                'precio' => 300,
                'css_variables' => [
                    '--primary-bg' => '#1a2e1a',
                    '--secondary-bg' => '#2d4a2d',
                    '--accent-color' => '#f0a500',
                    '--text-main' => '#e8f5e9',
                    '--text-muted' => '#a5d6a7',
                ],
                'preview_img' => 'assets/themes/forest-zen.png',
            ],
            [
                'nombre' => 'Midnight Ocean',
                'descripcion' => 'Profundidad azul para concentrarse mejor.',
                'precio' => 450,
                'css_variables' => [
                    '--primary-bg' => '#001220',
                    '--secondary-bg' => '#002233',
                    '--accent-color' => '#ffdd00',
                    '--text-main' => '#ffffff',
                    '--text-muted' => '#446688',
                ],
                'preview_img' => 'assets/themes/midnight-ocean.png',
            ],
        ];

        foreach ($temas as $tema) {
            Tema::create($tema);
        }
    }
}
