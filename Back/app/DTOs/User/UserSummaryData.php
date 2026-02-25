<?php

namespace App\DTOs\User;

readonly class UserSummaryData
{
    public function __construct(
        public string $nickname,
        public string $email,
        public ?string $avatar,
        public int $level,
        public int $experience,
        public int $coins,
        public int $streak,
        public int $nAchievements,
        public int $storyLevelsCompleted,
        public int $totalStoryLevels,
        public ?string $lastStoryLevelTitle,
        public int $roguelikeLevelsPlayed,
        public string $subscriptionDate,
        public int $rank,
        public bool $isPremium,
        public ?int $temaActualId,
        public array $preferencias,
        public array $nuevosLogros = []
    ) {
    }

    public function toArray(): array
    {
        return [
            'nickname' => $this->nickname,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'level' => $this->level,
            'experience' => $this->experience,
            'coins' => $this->coins,
            'streak' => $this->streak,
            'n_achievements' => $this->nAchievements,
            'story_levels_completed' => $this->storyLevelsCompleted,
            'total_story_levels' => $this->totalStoryLevels,
            'last_story_level_title' => $this->lastStoryLevelTitle,
            'roguelike_levels_played' => $this->roguelikeLevelsPlayed,
            'subscription_date' => $this->subscriptionDate,
            'rank' => $this->rank,
            'is_premium' => $this->isPremium,
            'tema_actual_id' => $this->temaActualId,
            'preferencias' => $this->preferencias,
            'nuevos_logros' => $this->nuevosLogros,
        ];
    }
}
