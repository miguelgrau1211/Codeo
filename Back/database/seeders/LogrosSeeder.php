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
                'requisito_operador' => '>=',
                'requisito_cantidad' => 1,
            ],
            [
                'nombre' => 'Primer Paso',
                'descripcion' => 'Juega tu primera partida en modo Roguelike.',
                'icono_filename' => 'primerLogro-Photoroom.png',
                'rareza' => 'especial',
                'requisito_tipo' => 'partidas_roguelike',
                'requisito_operador' => '>=',
                'requisito_cantidad' => 1,
            ],
            [
                'nombre' => 'Curioso',
                'descripcion' => 'Visita tu perfil por primera vez.',
                'icono_filename' => 'visitaPerfil-Photoroom.png',
                'rareza' => 'especial',
                'requisito_tipo' => 'visitar_perfil',
                'requisito_operador' => '>=',
                'requisito_cantidad' => 1,
            ],
            [
                'nombre' => 'Aprendiz de PHP',
                'descripcion' => 'Completa 5 niveles del modo historia.',
                'icono_filename' => 'nivel5.png',
                'rareza' => 'especial',
                'requisito_tipo' => 'nivel_historia',
                'requisito_operador' => '>=',
                'requisito_cantidad' => 5,
            ],
            [
                'nombre' => 'Primera Moneda',
                'descripcion' => 'Consigue tu primera moneda.',
                'icono_filename' => 'primeraMoneda.png',
                'rareza' => 'especial',
                'requisito_tipo' => 'monedas',
                'requisito_operador' => '>=',
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
                'requisito_operador' => '>=',
                'requisito_cantidad' => 1000,
            ],
            [
                'nombre' => 'Racha Imparable',
                'descripcion' => 'Mantén una racha de 7 días seguidos jugando.',
                'icono_filename' => 'racha7.png',
                'rareza' => 'raro',
                'requisito_tipo' => 'streak',
                'requisito_operador' => '>=',
                'requisito_cantidad' => 7,
            ],
            [
                'nombre' => 'Código Limpio',
                'descripcion' => 'Completa 10 niveles sin fallar ningún intento.',
                'icono_filename' => 'medalla-Photoroom.png',
                'rareza' => 'raro',
                'requisito_tipo' => 'niveles_sin_fallar',
                'requisito_operador' => '>=',
                'requisito_cantidad' => 10,
            ],
            [
                'nombre' => 'Nivel 10',
                'descripcion' => 'Alcanza el nivel global 10.',
                'icono_filename' => 'nivel5.png',
                'rareza' => 'raro',
                'requisito_tipo' => 'nivel_global',
                'requisito_operador' => '>=',
                'requisito_cantidad' => 10,
            ],
            [
                'nombre' => 'Explorador de Historia',
                'descripcion' => 'Completa 15 niveles del modo historia.',
                'icono_filename' => 'todosNiveles-Photoroom.png',
                'rareza' => 'raro',
                'requisito_tipo' => 'nivel_historia',
                'requisito_operador' => '>=',
                'requisito_cantidad' => 15,
            ],
            [
                'nombre' => 'Cazarrecompensas',
                'descripcion' => 'Desbloquea 5 logros.',
                'icono_filename' => '15logros-Photoroom.png',
                'rareza' => 'raro',
                'requisito_tipo' => 'logros',
                'requisito_operador' => '>=',
                'requisito_cantidad' => 5,
            ],
            [
                'nombre' => 'Resistencia',
                'descripcion' => 'Sobrevive 10 rondas en una partida Roguelike.',
                'icono_filename' => '25roguelike-Photoroom.png',
                'rareza' => 'raro',
                'requisito_tipo' => 'rondas_roguelike',
                'requisito_operador' => '>=',
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
                'requisito_operador' => '>=',
                'requisito_cantidad' => 50,
            ],
            [
                'nombre' => 'Maestro del Código',
                'descripcion' => 'Alcanza el nivel global 20.',
                'icono_filename' => 'nivel20-Photoroom.png',
                'rareza' => 'epico',
                'requisito_tipo' => 'nivel_global',
                'requisito_operador' => '>=',
                'requisito_cantidad' => 20,
            ],
            [
                'nombre' => 'Fortuna',
                'descripcion' => 'Acumula un total de 5000 monedas.',
                'icono_filename' => 'sacoMonedas-Photoroom.png',
                'rareza' => 'epico',
                'requisito_tipo' => 'monedas',
                'requisito_operador' => '>=',
                'requisito_cantidad' => 5000,
            ],
            [
                'nombre' => 'Racha de Fuego',
                'descripcion' => 'Mantén una racha de 30 días seguidos jugando.',
                'icono_filename' => 'racha30-Photoroom.png',
                'rareza' => 'epico',
                'requisito_tipo' => 'streak',
                'requisito_operador' => '>=',
                'requisito_cantidad' => 30,
            ],
            [
                'nombre' => 'Sin Errores',
                'descripcion' => 'Completa 25 niveles consecutivos sin fallar.',
                'icono_filename' => '25niveles-Photoroom.png',
                'rareza' => 'epico',
                'requisito_tipo' => 'niveles_sin_fallar',
                'requisito_operador' => '>=',
                'requisito_cantidad' => 25,
            ],
            [
                'nombre' => 'Historiador',
                'descripcion' => 'Completa todos los niveles del modo historia.',
                'icono_filename' => 'todosNiveles-Photoroom.png',
                'rareza' => 'epico',
                'requisito_tipo' => 'nivel_historia',
                'requisito_operador' => '>=',
                'requisito_cantidad' => 30, // Ajustar según total de niveles
            ],
            [
                'nombre' => 'Superviviente',
                'descripcion' => 'Sobrevive 25 rondas en una partida Roguelike.',
                'icono_filename' => '25roguelike-Photoroom.png',
                'rareza' => 'epico',
                'requisito_tipo' => 'rondas_roguelike',
                'requisito_operador' => '>=',
                'requisito_cantidad' => 25,
            ],
            [
                'nombre' => 'Coleccionista',
                'descripcion' => 'Desbloquea 15 logros.',
                'icono_filename' => '15logros-Photoroom.png',
                'rareza' => 'epico',
                'requisito_tipo' => 'logros',
                'requisito_operador' => '>=',
                'requisito_cantidad' => 15,
            ],

            // =============================================
            // 🟡 LEGENDARIO — Logros de élite
            // =============================================
            [
                'nombre' => 'PHP Senior',
                'descripcion' => 'Alcanza el nivel global 50.',
                'icono_filename' => 'nivel50-Photoroom.png',
                'rareza' => 'legendario',
                'requisito_tipo' => 'nivel_global',
                'requisito_operador' => '>=',
                'requisito_cantidad' => 50,
            ],
            [
                'nombre' => 'Magnate',
                'descripcion' => 'Acumula un total de 25000 monedas.',
                'icono_filename' => 'logro25kMonedas-Photoroom.png',
                'rareza' => 'legendario',
                'requisito_tipo' => 'monedas',
                'requisito_operador' => '>=',
                'requisito_cantidad' => 25000,
            ],
            [
                'nombre' => 'Máquina Imparable',
                'descripcion' => 'Juega 200 partidas en modo Roguelike.',
                'icono_filename' => '200partidasRoguelike-Photoroom.png',
                'rareza' => 'legendario',
                'requisito_tipo' => 'partidas_roguelike',
                'requisito_operador' => '>=',
                'requisito_cantidad' => 200,
            ],
            [
                'nombre' => 'Racha Eterna',
                'descripcion' => 'Mantén una racha de 100 días seguidos jugando.',
                'icono_filename' => '100racha-Photoroom.png',
                'rareza' => 'legendario',
                'requisito_tipo' => 'streak',
                'requisito_operador' => '>=',
                'requisito_cantidad' => 100,
            ],
            [
                'nombre' => 'Gladiador',
                'descripcion' => 'Sobrevive 50 rondas en una partida Roguelike.',
                'icono_filename' => '50roguelike-Photoroom.png',
                'rareza' => 'legendario',
                'requisito_tipo' => 'rondas_roguelike',
                'requisito_operador' => '>=',
                'requisito_cantidad' => 50,
            ],
            [
                'nombre' => 'Perfeccionista',
                'descripcion' => 'Completa 50 niveles sin fallar ningún intento.',
                'icono_filename' => '50niveles-Photoroom.png',
                'rareza' => 'legendario',
                'requisito_tipo' => 'niveles_sin_fallar',
                'requisito_operador' => '>=',
                'requisito_cantidad' => 50,
            ],
            [
                'nombre' => 'Ciudadano Ejemplar',
                'descripcion' => 'Desbloquea 25 logros.',
                'icono_filename' => '25logros-Photoroom.png',
                'rareza' => 'legendario',
                'requisito_tipo' => 'logros',
                'requisito_operador' => '>=',
                'requisito_cantidad' => 25,
            ],

            // =============================================
            // ✨ CELESTIAL — Logros casi imposibles / secretos
            // =============================================
            [
                'nombre' => 'Arquitecto Divino',
                'descripcion' => 'Alcanza el nivel global 100.',
                'icono_filename' => 'nivel100-Photoroom.png',
                'rareza' => 'celestial',
                'requisito_tipo' => 'nivel_global',
                'requisito_operador' => '>=',
                'requisito_cantidad' => 100,
            ],
            [
                'nombre' => 'El 1%',
                'descripcion' => 'Acumula un total de 100000 monedas.',
                'icono_filename' => 'muchasMonedas-Photoroom.png',
                'rareza' => 'celestial',
                'requisito_tipo' => 'monedas',
                'requisito_operador' => '>=',
                'requisito_cantidad' => 100000,
            ],
            [
                'nombre' => 'Inmortal',
                'descripcion' => 'Sobrevive 100 rondas en una partida Roguelike.',
                'icono_filename' => '100rondas-Photoroom.png',
                'rareza' => 'celestial',
                'requisito_tipo' => 'rondas_roguelike',
                'requisito_operador' => '>=',
                'requisito_cantidad' => 100,
            ],
            [
                'nombre' => 'Racha Infinita',
                'descripcion' => 'Mantén una racha de 365 días seguidos. Un año entero.',
                'icono_filename' => '365racha-Photoroom.png',
                'rareza' => 'celestial',
                'requisito_tipo' => 'streak',
                'requisito_operador' => '>=',
                'requisito_cantidad' => 365,
            ],
            [
                'nombre' => 'Leyenda de Codeo',
                'descripcion' => 'Desbloquea todos los logros del juego.',
                'icono_filename' => 'platinoCodeo-Photoroom.png',
                'rareza' => 'celestial',
                'requisito_tipo' => 'logros_todos',
                'requisito_operador' => '>=',
                'requisito_cantidad' => 1,
            ],
            [
                'nombre' => 'Easter Egg',
                'descripcion' => 'Encuentra el easter egg oculto.',
                'icono_filename' => 'huevo-Photoroom.png',
                'rareza' => 'celestial',
                'requisito_tipo' => 'easter_egg',
                'requisito_operador' => '>=',
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
                    $dbPath = $storageDir . '/' . $filename;
                }
            }

            // Eliminar campo temporal y asignar ruta final
            unset($data['icono_filename']);
            $data['icono_url'] = $dbPath;

            Logros::updateOrCreate(
                ['nombre' => $data['nombre']],
                $data
            );
        }
    }
}
