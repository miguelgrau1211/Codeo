<?php

namespace App\Actions\User;

use App\Models\Usuario;
use App\Models\UsuarioLogro;
use App\Models\ProgresoHistoria;
use App\Models\NivelesHistoria;
use App\Models\RunsRoguelike;
use App\Actions\Achievements\CheckAchievementsAction;
use App\Actions\UpdateUserStreakAction;
use App\Actions\GrantBattlePassRewardsAction;
use App\Services\TranslationService;
use App\DTOs\User\UserSummaryData;
use Illuminate\Http\Request;

class GetUserSummaryAction
{
    public function execute(Usuario $usuario, Request $request): UserSummaryData
    {
        // 1. Lógica que SIEMPRE debe ejecutarse (actualizaciones de estado)
        (new UpdateUserStreakAction())->checkAndResetIfNecessary($usuario);
        (new GrantBattlePassRewardsAction())->execute($usuario);

        // 2. Usar caché para datos de lectura pesados (Ranking, Estadísticas)
        $cacheKey = "user_summary_{$usuario->id}";
        
        // Obtenemos los datos calculados (ya sea de caché o calculándolos ahora)
        $data = \Illuminate\Support\Facades\Cache::remember($cacheKey, 600, function () use ($usuario) {
            $nAchievements = UsuarioLogro::where('usuario_id', $usuario->id)->count();
            $storyLevelsCompleted = ProgresoHistoria::where('usuario_id', $usuario->id)->where('completado', true)->count();
            $roguelikeLevelsPlayed = (int) RunsRoguelike::where('usuario_id', $usuario->id)->sum('niveles_superados');
            $totalStoryLevels = NivelesHistoria::count();

            $lastLevelRecord = ProgresoHistoria::where('usuario_id', $usuario->id)
                ->where('completado', true)
                ->with('nivel')
                ->orderByDesc('updated_at')
                ->first();

            $rank = Usuario::where('exp_total', '>', $usuario->exp_total)->count() + 1;

            return [
                'nAchievements' => $nAchievements,
                'storyLevelsCompleted' => $storyLevelsCompleted,
                'roguelikeLevelsPlayed' => $roguelikeLevelsPlayed,
                'totalStoryLevels' => $totalStoryLevels,
                'lastStoryLevelTitle' => $lastLevelRecord?->nivel?->titulo,
                'rank' => $rank
            ];
        });

        // 3. Logros por visitar perfil (Esto puede dar nuevos logros, no se cachea el resultado final)
        $nuevosLogros = (new CheckAchievementsAction())->execute(['visitar_perfil' => 1]);
        $locale = TranslationService::resolveLocale($request);
        $translatedLogros = app(TranslationService::class)->translateLogrosCollection($nuevosLogros, $locale);

        return new UserSummaryData(
            nickname: $usuario->nickname,
            email: $usuario->email,
            avatar: $usuario->avatar_url,
            level: $usuario->nivel_global,
            experience: $usuario->exp_total,
            coins: $usuario->monedas,
            streak: $usuario->streak,
            nAchievements: $data['nAchievements'],
            storyLevelsCompleted: $data['storyLevelsCompleted'],
            totalStoryLevels: $data['totalStoryLevels'],
            lastStoryLevelTitle: $data['lastStoryLevelTitle'],
            roguelikeLevelsPlayed: $data['roguelikeLevelsPlayed'],
            subscriptionDate: $usuario->created_at->format('d/m/Y'),
            rank: $data['rank'],
            isPremium: (bool) $usuario->es_premium,
            temaActualId: $usuario->tema_actual_id,
            preferencias: $usuario->preferencias ?? [],
            nuevosLogros: $translatedLogros
        );
    }
}
