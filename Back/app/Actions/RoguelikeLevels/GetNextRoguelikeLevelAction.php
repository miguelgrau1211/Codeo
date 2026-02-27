<?php

namespace App\Actions\RoguelikeLevels;

use App\Models\NivelRoguelike;
use App\Services\TranslationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Acción para determinar y obtener el siguiente nivel en una sesión de Roguelike.
 * Gestiona la selección aleatoria basada en dificultad progresiva y evita repeticiones.
 */
class GetNextRoguelikeLevelAction
{
    /**
     * @param int $userId
     * @param string $locale
     * @return array Nivel traducido y datos de sesión actualizados.
     */
    public function execute(int $userId, string $locale): array
    {
        $cacheKey = "roguelike_session_" . $userId;
        $session = Cache::get($cacheKey);
        $translator = app(TranslationService::class);

        // 1. Si no hay sesión, devolvemos un nivel aleatorio simple (fallback)
        if (!$session) {
            $nivel = NivelRoguelike::inRandomOrder()->first() ?? $this->getEmptyLevelFallback();
            return ['nivel' => $translator->translateNivel($nivel, $locale)];
        }

        // 2. Si ya hay un nivel asignado a la sesión (recarga), devolvemos ese
        if (!empty($session['current_level_id'])) {
            $nivel = NivelRoguelike::find($session['current_level_id']);
            if ($nivel) {
                return ['nivel' => $translator->translateNivel($nivel, $locale)];
            }
        }

        // 3. Determinar dificultad según progreso
        $nivelesCompletados = $session['levels_completed'] ?? 0;
        $dificultad = $this->determineDifficulty($nivelesCompletados);
        $usados = $session['used_level_ids'] ?? [];

        // 4. Buscar nivel no usado
        $nivel = NivelRoguelike::where('dificultad', $dificultad)
            ->whereNotIn('id', $usados)
            ->inRandomOrder()
            ->first();

        // Fallback si se agotan los niveles de esa dificultad
        if (!$nivel) {
            $nivel = NivelRoguelike::whereNotIn('id', $usados)->inRandomOrder()->first();
        }

        // Último recurso: permitir repetición si se han jugado absolutamente todos
        if (!$nivel) {
            $nivel = NivelRoguelike::inRandomOrder()->first() ?? $this->getEmptyLevelFallback();
        }

        // 5. Actualizar sesión con el nuevo nivel asignado
        $session['current_level_id'] = $nivel->id;
        Cache::put($cacheKey, $session, 7200);

        return ['nivel' => $translator->translateNivel($nivel, $locale)];
    }

    /**
     * Probabilidades de dificultad basadas en el nivel actual de la run.
     */
    private function determineDifficulty(int $completed): string
    {
        $rand = rand(1, 100);

        if ($completed < 4) {
            // Early game: Mayoría fácil
            return $rand <= 80 ? 'fácil' : 'medio';
        } elseif ($completed < 8) {
            // Mid game: Mayoría medio
            if ($rand <= 40)
                return 'fácil';
            if ($rand <= 90)
                return 'medio';
            return 'difícil';
        } else {
            // Late game: Difícil y Extremo
            if ($rand <= 20)
                return 'fácil';
            if ($rand <= 70)
                return 'medio';
            return 'difícil';
        }
    }

    private function getEmptyLevelFallback(): NivelRoguelike
    {
        return new NivelRoguelike([
            'id' => 9999,
            'titulo' => 'Desafío Genérico',
            'descripcion' => 'No se encontraron niveles específicos. Crea una función que devuelva el string "hola".',
            'dificultad' => 'fácil',
            'test_cases' => [['input' => '', 'output' => '"hola"']],
            'recompensa_monedas' => 10
        ]);
    }
}
