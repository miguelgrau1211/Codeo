<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Models\Logros;

class LogrosSeeder extends Seeder
{
    public function run(): void
    {
        // Rutas de origen (seed content) y destino (storage public)
        $sourceDir = database_path('seeders/content/logros');
        $storageDir = 'logros';

        $logros = [

            // =============================================
            // 🟢 ESPECIAL — Logros de iniciación / sencillos
            // =============================================
            [
                'nombre' => '¡Hola Mundo!',
                'descripcion' => 'Completa el primer nivel del modo historia.',
                'icono_filename' => 'medalla-Photoroom.png',
                'rareza' => 'especial',
                'requisito_tipo' => 'nivel_historia',
                'requisito_cantidad' => 1,
            ],
            [
                'nombre' => 'Primer Paso',
                'descripcion' => 'Juega tu primera partida en modo Roguelike.',
                'icono_filename' => 'primerLogro-Photoroom.png',
                'rareza' => 'especial',
                'requisito_tipo' => 'partidas_roguelike',
                'requisito_cantidad' => 1,
            ],
            [
                'nombre' => 'Curioso',
                'descripcion' => 'Visita tu perfil por primera vez.',
                'icono_filename' => 'new.png',
                'rareza' => 'especial',
                'requisito_tipo' => 'visitar_perfil',
                'requisito_cantidad' => 1,
            ],
            [
                'nombre' => 'Aprendiz de PHP',
                'descripcion' => 'Completa 5 niveles del modo historia.',
                'icono_filename' => null,
                'rareza' => 'especial',
                'requisito_tipo' => 'nivel_historia',
                'requisito_cantidad' => 5,
            ],
            [
                'nombre' => 'Primera Moneda',
                'descripcion' => 'Consigue tu primera moneda.',
                'icono_filename' => null,
                'rareza' => 'especial',
                'requisito_tipo' => 'monedas',
                'requisito_cantidad' => 1,
            ],

            // =============================================
            // 🔵 RARO — Logros de compromiso medio
            // =============================================
            [
                'nombre' => 'Ahorrador Compulsivo',
                'descripcion' => 'Acumula un total de 1000 monedas.',
                'icono_filename' => 'monedas-Photoroom.png',
                'rareza' => 'raro',
                'requisito_tipo' => 'monedas',
                'requisito_cantidad' => 1000,
            ],
            [
                'nombre' => 'Racha Imparable',
                'descripcion' => 'Mantén una racha de 7 días seguidos jugando.',
                'icono_filename' => null,
                'rareza' => 'raro',
                'requisito_tipo' => 'racha_dias',
                'requisito_cantidad' => 7,
            ],
            [
                'nombre' => 'Código Limpio',
                'descripcion' => 'Completa 10 niveles sin fallar ningún intento.',
                'icono_filename' => null,
                'rareza' => 'raro',
                'requisito_tipo' => 'niveles_sin_fallar',
                'requisito_cantidad' => 10,
            ],
            [
                'nombre' => 'Nivel 10',
                'descripcion' => 'Alcanza el nivel global 10.',
                'icono_filename' => null,
                'rareza' => 'raro',
                'requisito_tipo' => 'nivel_global',
                'requisito_cantidad' => 10,
            ],
            [
                'nombre' => 'Explorador de Historia',
                'descripcion' => 'Completa 15 niveles del modo historia.',
                'icono_filename' => null,
                'rareza' => 'raro',
                'requisito_tipo' => 'nivel_historia',
                'requisito_cantidad' => 15,
            ],
            [
                'nombre' => 'Cazarrecompensas',
                'descripcion' => 'Desbloquea 5 logros.',
                'icono_filename' => null,
                'rareza' => 'raro',
                'requisito_tipo' => 'logros',
                'requisito_cantidad' => 5,
            ],
            [
                'nombre' => 'Resistencia',
                'descripcion' => 'Sobrevive 10 rondas en una partida Roguelike.',
                'icono_filename' => null,
                'rareza' => 'raro',
                'requisito_tipo' => 'rondas_roguelike',
                'requisito_cantidad' => 10,
            ],

            // =============================================
            // 🟣 ÉPICO — Logros de dedicación seria
            // =============================================
            [
                'nombre' => 'Veterano de Guerra',
                'descripcion' => 'Juega 50 partidas en modo Roguelike.',
                'icono_filename' => '50partidas-Photoroom.png',
                'rareza' => 'epico',
                'requisito_tipo' => 'partidas_roguelike',
                'requisito_cantidad' => 50,
            ],
            [
                'nombre' => 'Maestro del Código',
                'descripcion' => 'Alcanza el nivel global 20.',
                'icono_filename' => 'nivel20-Photoroom.png',
                'rareza' => 'epico',
                'requisito_tipo' => 'nivel_global',
                'requisito_cantidad' => 20,
            ],
            [
                'nombre' => 'Fortuna',
                'descripcion' => 'Acumula un total de 5000 monedas.',
                'icono_filename' => null,
                'rareza' => 'epico',
                'requisito_tipo' => 'monedas',
                'requisito_cantidad' => 5000,
            ],
            [
                'nombre' => 'Racha de Fuego',
                'descripcion' => 'Mantén una racha de 30 días seguidos jugando.',
                'icono_filename' => null,
                'rareza' => 'epico',
                'requisito_tipo' => 'racha_dias',
                'requisito_cantidad' => 30,
            ],
            [
                'nombre' => 'Sin Errores',
                'descripcion' => 'Completa 25 niveles consecutivos sin fallar.',
                'icono_filename' => null,
                'rareza' => 'epico',
                'requisito_tipo' => 'niveles_sin_fallar',
                'requisito_cantidad' => 25,
            ],
            [
                'nombre' => 'Historiador',
                'descripcion' => 'Completa todos los niveles del modo historia.',
                'icono_filename' => null,
                'rareza' => 'epico',
                'requisito_tipo' => 'completar_historia',
                'requisito_cantidad' => 1,
            ],
            [
                'nombre' => 'Superviviente',
                'descripcion' => 'Sobrevive 25 rondas en una partida Roguelike.',
                'icono_filename' => null,
                'rareza' => 'epico',
                'requisito_tipo' => 'rondas_roguelike',
                'requisito_cantidad' => 25,
            ],
            [
                'nombre' => 'Coleccionista',
                'descripcion' => 'Desbloquea 15 logros.',
                'icono_filename' => null,
                'rareza' => 'epico',
                'requisito_tipo' => 'logros',
                'requisito_cantidad' => 15,
            ],

            // =============================================
            // 🟡 LEGENDARIO — Logros de élite
            // =============================================
            [
                'nombre' => 'PHP Senior',
                'descripcion' => 'Alcanza el nivel global 50.',
                'icono_filename' => null,
                'rareza' => 'legendario',
                'requisito_tipo' => 'nivel_global',
                'requisito_cantidad' => 50,
            ],
            [
                'nombre' => 'Magnate',
                'descripcion' => 'Acumula un total de 25000 monedas.',
                'icono_filename' => null,
                'rareza' => 'legendario',
                'requisito_tipo' => 'monedas',
                'requisito_cantidad' => 25000,
            ],
            [
                'nombre' => 'Máquina Imparable',
                'descripcion' => 'Juega 200 partidas en modo Roguelike.',
                'icono_filename' => null,
                'rareza' => 'legendario',
                'requisito_tipo' => 'partidas_roguelike',
                'requisito_cantidad' => 200,
            ],
            [
                'nombre' => 'Racha Eterna',
                'descripcion' => 'Mantén una racha de 100 días seguidos jugando.',
                'icono_filename' => null,
                'rareza' => 'legendario',
                'requisito_tipo' => 'racha_dias',
                'requisito_cantidad' => 100,
            ],
            [
                'nombre' => 'Gladiador',
                'descripcion' => 'Sobrevive 50 rondas en una partida Roguelike.',
                'icono_filename' => null,
                'rareza' => 'legendario',
                'requisito_tipo' => 'rondas_roguelike',
                'requisito_cantidad' => 50,
            ],
            [
                'nombre' => 'Perfeccionista',
                'descripcion' => 'Completa 50 niveles sin fallar ningún intento.',
                'icono_filename' => null,
                'rareza' => 'legendario',
                'requisito_tipo' => 'niveles_sin_fallar',
                'requisito_cantidad' => 50,
            ],
            [
                'nombre' => 'Ciudadano Ejemplar',
                'descripcion' => 'Desbloquea 25 logros.',
                'icono_filename' => null,
                'rareza' => 'legendario',
                'requisito_tipo' => 'logros',
                'requisito_cantidad' => 25,
            ],

            // =============================================
            // ✨ CELESTIAL — Logros casi imposibles / secretos
            // =============================================
            [
                'nombre' => 'Arquitecto Divino',
                'descripcion' => 'Alcanza el nivel global 100.',
                'icono_filename' => null,
                'rareza' => 'celestial',
                'requisito_tipo' => 'nivel_global',
                'requisito_cantidad' => 100,
            ],
            [
                'nombre' => 'El 1%',
                'descripcion' => 'Acumula un total de 100000 monedas.',
                'icono_filename' => null,
                'rareza' => 'celestial',
                'requisito_tipo' => 'monedas',
                'requisito_cantidad' => 100000,
            ],
            [
                'nombre' => 'Inmortal',
                'descripcion' => 'Sobrevive 100 rondas en una partida Roguelike.',
                'icono_filename' => null,
                'rareza' => 'celestial',
                'requisito_tipo' => 'rondas_roguelike',
                'requisito_cantidad' => 100,
            ],
            [
                'nombre' => 'Racha Infinita',
                'descripcion' => 'Mantén una racha de 365 días seguidos. Un año entero.',
                'icono_filename' => null,
                'rareza' => 'celestial',
                'requisito_tipo' => 'racha_dias',
                'requisito_cantidad' => 365,
            ],
            [
                'nombre' => 'Leyenda de Codeo',
                'descripcion' => 'Desbloquea todos los logros del juego.',
                'icono_filename' => null,
                'rareza' => 'celestial',
                'requisito_tipo' => 'logros_todos',
                'requisito_cantidad' => 1,
            ],
            [
                'nombre' => 'Easter Egg',
                'descripcion' => 'Encuentra el easter egg oculto.',
                'icono_filename' => 'huevo-Photoroom.png',
                'rareza' => 'celestial',
                'requisito_tipo' => 'easter_egg',
                'requisito_cantidad' => 1,
            ],
        ];

        foreach ($logros as $data) {
            $filename = $data['icono_filename'];
            $dbPath = null;

            // Si tiene imagen definida y el archivo existe, copiarlo al disco público
            if ($filename) {
                $sourcePath = $sourceDir . '/' . $filename;

                if (File::exists($sourcePath)) {
                    Storage::disk('public')->putFileAs(
                        $storageDir,
                        new \Illuminate\Http\File($sourcePath),
                        $filename
                    );
                    $dbPath = Storage::url($storageDir . '/' . $filename);
                }
            }

            // Eliminar campo temporal y asignar ruta final
            unset($data['icono_filename']);
            $data['icono_url'] = $dbPath;

            Logros::firstOrCreate(
                ['nombre' => $data['nombre']],
                $data
            );
        }
    }
}
