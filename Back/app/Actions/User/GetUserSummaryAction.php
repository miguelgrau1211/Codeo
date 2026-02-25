<?php

namespace App\Actions\User;

use App\Models\Usuario;
use App\Models\UsuarioLogro;
use App\Models\ProgresoHistoria;
use App\Models\NivelesHistoria;
use App\Models\RunsRoguelike;
use App\Actions\CheckAchievementsAction;
use App\Actions\UpdateUserStreakAction;
use App\Actions\GrantBattlePassRewardsAction;
use App\Services\TranslationService;
use App\DTOs\User\UserSummaryData;
use Illuminate\Http\Request;

class GetUserSummaryAction
{
    public function execute(Usuario $usuario, Request $request): UserSummaryData
    {
        // Actualizar racha y recompensas
        (new UpdateUserStreakAction())->checkAndResetIfNecessary($usuario);
        (new GrantBattlePassRewardsAction())->execute($usuario);

        // Estadísticas básicas
        $nAchievements = UsuarioLogro::where('usuario_id', $usuario->id)->count();
        $storyLevelsCompleted = ProgresoHistoria::where('usuario_id', $usuario->id)->where('completado', true)->count();
        $roguelikeLevelsPlayed = (int) RunsRoguelike::where('usuario_id', $usuario->id)->sum('niveles_superados');
        $totalStoryLevels = NivelesHistoria::count();

        // Último nivel completado
        $lastLevelRecord = ProgresoHistoria::where('usuario_id', $usuario->id)
            ->where('completado', true)
            ->with('nivel')
            ->orderByDesc('updated_at')
            ->first();

        $lastStoryLevelTitle = $lastLevelRecord?->nivel?->titulo;

        // Ranking
        $rank = Usuario::where('exp_total', '>', $usuario->exp_total)->count() + 1;

        // Logros por visitar perfil
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
            nAchievements: $nAchievements,
            storyLevelsCompleted: $storyLevelsCompleted,
            totalStoryLevels: $totalStoryLevels,
            lastStoryLevelTitle: $lastStoryLevelTitle,
            roguelikeLevelsPlayed: $roguelikeLevelsPlayed,
            subscriptionDate: $usuario->created_at->format('d/m/Y'),
            rank: $rank,
            isPremium: (bool) $usuario->es_premium,
            temaActualId: $usuario->tema_actual_id,
            preferencias: $usuario->preferencias ?? [],
            nuevosLogros: $translatedLogros
        );
    }
}
