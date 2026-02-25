<?php

namespace App\Actions\User;

use App\Models\Usuario;
use App\Models\ProgresoHistoria;
use App\Models\UsuarioLogro;
use App\Models\RunsRoguelike;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class GetUserRecentActivityAction
{
    public function execute(Usuario $usuario, int $limit = 5): Collection
    {
        $id = $usuario->id;

        // 1. Historia
        $historia = ProgresoHistoria::where('usuario_id', $id)
            ->where('completado', true)
            ->with('nivel')
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($item) => [
                'titulo' => $item->nivel?->titulo ?? 'Nivel desconocido',
                'subtitulo' => 'PROFILE.ACTIVITY.STORY_COMPLETE',
                'xp' => $item->nivel?->recompensa_exp ?? 0,
                'tipo' => 'historia',
                'fecha_raw' => $item->updated_at,
                'fecha' => $item->updated_at->diffForHumans()
            ]);

        // 2. Logros
        $logros = UsuarioLogro::where('usuario_id', $id)
            ->with('logro')
            ->orderBy('fecha_desbloqueo', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($item) => [
                'titulo' => $item->logro?->nombre ?? 'Logro desconocido',
                'subtitulo' => 'PROFILE.ACTIVITY.ACHIEVEMENT_UNLOCKED',
                'xp' => 0,
                'tipo' => 'logro',
                'fecha_raw' => Carbon::parse($item->fecha_desbloqueo),
                'fecha' => Carbon::parse($item->fecha_desbloqueo)->diffForHumans()
            ]);

        // 3. Roguelike
        $runs = RunsRoguelike::where('usuario_id', $id)
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $dataPartida = $item->data_partida;
                $data = is_string($dataPartida) ? json_decode($dataPartida, true) : $dataPartida;
                $xp = $data['xp_earned'] ?? ($item->monedas_obtenidas * 2);

                return [
                    'titulo' => "Run Roguelike (Nivel {$item->niveles_superados})",
                    'subtitulo' => 'PROFILE.ACTIVITY.ROGUELIKE_RUN',
                    'xp' => $xp,
                    'tipo' => 'roguelike',
                    'fecha_raw' => $item->updated_at,
                    'fecha' => $item->updated_at->diffForHumans()
                ];
            });

        // Merge & Sort
        return collect($historia)
            ->merge($logros)
            ->merge($runs)
            ->sortByDesc('fecha_raw')
            ->take($limit)
            ->map(function ($item) {
                unset($item['fecha_raw']);
                return $item;
            })
            ->values();
    }
}
