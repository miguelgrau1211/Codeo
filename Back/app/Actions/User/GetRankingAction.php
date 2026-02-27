<?php

namespace App\Actions\User;

use App\Models\Usuario;

/**
 * Acción para obtener el ranking de usuarios paginado.
 * Extrae la lógica de consulta y formateo del ranking fuera del controlador.
 */
class GetRankingAction
{
    /**
     * Ejecuta la lógica para obtener el ranking.
     * 
     * @return array
     */
    public function execute(): array
    {
        $ranking = Usuario::select('nickname', 'avatar_url', 'nivel_global', 'exp_total')
            ->orderByDesc('exp_total')
            ->paginate(10);

        $items = $ranking->getCollection()->map(function ($usuario, $key) use ($ranking) {
            return [
                'posicion' => (($ranking->currentPage() - 1) * $ranking->perPage()) + $key + 1,
                'nickname' => $usuario->nickname,
                'avatar_url' => $usuario->avatar_url ?? 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . $usuario->nickname,
                'nivel' => $usuario->nivel_global ?? 1,
                'puntos' => $usuario->exp_total ?? 0
            ];
        });

        return [
            'pagina_actual' => $ranking->currentPage(),
            'total_paginas' => $ranking->lastPage(),
            'usuarios' => $items
        ];
    }
}
