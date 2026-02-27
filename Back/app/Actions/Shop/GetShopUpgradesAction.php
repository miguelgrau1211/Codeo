<?php

namespace App\Actions\Shop;

use App\Models\Mejoras;
use App\Services\TranslationService;

/**
 * Acción para listar mejoras de la tienda con soporte multiidioma.
 */
class GetShopUpgradesAction
{
    /**
     * @param string $locale
     * @param bool $random Solo 3 aleatorias (para la tienda de sesión).
     * @return array
     */
    public function execute(string $locale, bool $random = false): array
    {
        $query = Mejoras::query();

        if ($random) {
            $query->inRandomOrder()->take(3);
        }

        $mejoras = $query->get();

        // Asumiendo que TranslationService tiene soporte para 'mejora'
        // Si no lo tiene, devolvemos el original
        try {
            return app(TranslationService::class)->translateCollection($mejoras, $locale, 'mejora');
        } catch (\Throwable $e) {
            return $mejoras->toArray();
        }
    }
}
