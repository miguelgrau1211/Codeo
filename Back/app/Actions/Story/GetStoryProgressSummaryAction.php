<?php

namespace App\Actions\Story;

use App\Models\ProgresoHistoria;
use App\Models\NivelesHistoria;
use App\Services\TranslationService;

/**
 * Acción para recopilar el progreso detallado del modo historia de un usuario.
 */
class GetStoryProgressSummaryAction
{
    /**
     * @param int $userId
     * @param string $locale
     * @return array
     */
    public function execute(int $userId, string $locale): array
    {
        $niveles = NivelesHistoria::orderBy('orden')->get();
        $progresoUsuario = ProgresoHistoria::where('usuario_id', $userId)->get()->keyBy('nivel_id');

        $translator = app(TranslationService::class);
        $nivelesTraducidos = collect($translator->translateCollection($niveles, $locale, 'nivel'))->keyBy('id');

        $progresoDetallado = $niveles->map(function ($nivel) use ($progresoUsuario, $userId, $nivelesTraducidos) {
            $progreso = $progresoUsuario->get($nivel->id);
            $translated = $nivelesTraducidos->get($nivel->id);

            return [
                'id' => $progreso?->id,
                'nivel_id' => $nivel->id,
                'completado' => (bool) ($progreso?->completado ?? false),
                'codigo_inicial' => $translated['codigo_inicial'],
                'codigo_solucion_usuario' => $progreso?->codigo_solucion_usuario ?? $translated['codigo_inicial'],
                'titulo' => $translated['titulo'],
                'orden' => $nivel->orden,
                'descripcion' => $translated['descripcion'],
                'contenido_teorico' => $translated['contenido_teorico'],
                'test_cases' => $nivel->test_cases,
            ];
        });

        // Estadísticas rápidas
        $totalNiveles = $niveles->count();
        $completados = $progresoUsuario->where('completado', 1)->count();

        return [
            'stats' => [
                'total_niveles' => $totalNiveles,
                'completados' => $completados,
                'porcentaje' => $totalNiveles > 0 ? round(($completados / $totalNiveles) * 100) . '%' : '0%',
            ],
            'niveles' => $progresoDetallado
        ];
    }
}
