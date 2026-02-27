<?php

namespace App\Actions\Achievements;

use App\Models\Logros;
use App\Models\UsuarioLogro;
use App\Models\ProgresoHistoria;
use App\Models\RunsRoguelike;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckAchievementsAction
{
    /**
     * Revisa si el usuario ha cumplido los requisitos para nuevos logros.
     * 
     * @param array $manualStats Estadísticas manuales para disparar logros específicos (ej: ['visitar_perfil' => 1])
     * @return array Lista de logros recién desbloqueados
     */
    public function execute(array $manualStats = []): array
    {
        $usuario = Auth::user();
        if (!$usuario)
            return [];

        // 1. Obtener IDs de logros que el usuario ya tiene
        $logrosYaObtenidos = UsuarioLogro::where('usuario_id', $usuario->id)
            ->pluck('logro_id')
            ->toArray();

        $nuevosLogros = [];
        $hayNuevosEnEstaRonda = true;

        // 3. Métrica especial: total de logros que existen en el sistema
        $totalLogrosSistema = Logros::count();

        // 4. Cachear stats para evitar múltiples queries (inicialmente)
        $stats = array_merge([
            'monedas' => $usuario->monedas,
            'nivel_global' => $usuario->nivel_global,
            'streak' => $usuario->streak,
            'nivel_historia' => ProgresoHistoria::where('usuario_id', $usuario->id)->where('completado', true)->count(),
            'partidas_roguelike' => RunsRoguelike::where('usuario_id', $usuario->id)->count(),
            'rondas_roguelike' => RunsRoguelike::where('usuario_id', $usuario->id)->max('niveles_superados') ?? 0,
            'logros' => count($logrosYaObtenidos), // Initial count
            'total_logros_sistema' => $totalLogrosSistema,
        ], $manualStats);

        // Bucle de reacción: mientras se sigan desbloqueando logros, seguimos comprobando.
        // Esto permite que un logro ganado dispare a otro (ej: ganar el logro 15 dispara el logro de "Coleccionista")
        while ($hayNuevosEnEstaRonda) {
            $hayNuevosEnEstaRonda = false;

            // Refrescar la lista de logros que aún no tiene el usuario en cada vuelta
            $logrosYaObtenidosIds = UsuarioLogro::where('usuario_id', $usuario->id)
                ->pluck('logro_id')
                ->toArray();

            $logrosPendientes = Logros::whereNotIn('id', $logrosYaObtenidosIds)->get();

            // Actualizar contador de logros en las stats
            $stats['logros'] = count($logrosYaObtenidosIds);

            foreach ($logrosPendientes as $logro) {
                /** @var Logros $logro */
                if ($this->checkRequirement($logro, $stats)) {
                    // Desbloquear logro
                    UsuarioLogro::create([
                        'usuario_id' => $usuario->id,
                        'logro_id' => $logro->id,
                        'fecha_desbloqueo' => now()
                    ]);

                    $nuevosLogros[] = $logro;
                    $hayNuevosEnEstaRonda = true;

                    // Actualizar el contador inmediatamente para el siguiente logro en el mismo bucle
                    $stats['logros']++;
                }
            }
        }

        return $nuevosLogros;
    }

    /**
     * Verifica si se cumple el requisito de un logro específico.
     */
    private function checkRequirement($logro, array $stats): bool
    {
        $tipo = $logro->requisito_tipo;
        $operador = $logro->requisito_operador ?? '>=';
        $cantidad = $logro->requisito_cantidad;

        // Si la métrica pedida por la DB no existe en nuestro diccionario de stats, no se cumple.
        if (!isset($stats[$tipo])) {
            return false;
        }

        return $this->evaluate($stats[$tipo], $operador, $cantidad);
    }

    /**
     * Evalúa una comparación simple de forma segura.
     */
    private function evaluate($valorActual, string $operador, $valorObjetivo): bool
    {
        return match ($operador) {
            '>=' => $valorActual >= $valorObjetivo,
            '<=' => $valorActual <= $valorObjetivo,
            '==' => $valorActual == $valorObjetivo,
            '>' => $valorActual > $valorObjetivo,
            '<' => $valorActual < $valorObjetivo,
            '!=' => $valorActual != $valorObjetivo,
            default => $valorActual >= $valorObjetivo,
        };
    }
}
