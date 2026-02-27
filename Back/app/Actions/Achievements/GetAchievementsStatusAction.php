<?php

namespace App\Actions\Achievements;

use App\Models\Logros;
use App\Models\UsuarioLogro;
use App\Services\TranslationService;
use Illuminate\Support\Collection;

/**
 * Acción para obtener la lista completa de logros con su estado de desbloqueo.
 */
class GetAchievementsStatusAction
{
    /**
     * @param int $userId ID del usuario actual.
     * @param string $locale Idioma para la traducción.
     * @return array Resumen de progreso y lista detallada.
     */
    public function execute(int $userId, string $locale): array
    {
        // 1. Cargamos todos los logros y el progreso del usuario
        $todosLosLogros = Logros::all();
        $userLogros = UsuarioLogro::where('usuario_id', $userId)->get()->keyBy('logro_id');

        // 2. Traducción masiva
        $translator = app(TranslationService::class);
        $translated = collect($translator->translateCollection($todosLosLogros, $locale, 'logro'))->keyBy('id');

        // 3. Mapeo de datos combinados
        $lista = $todosLosLogros->map(function ($logro) use ($userLogros, $translated) {
            $desbloqueado = $userLogros->has($logro->id);
            $infoTraducida = $translated->get($logro->id);

            return [
                'id' => $logro->id,
                'nombre' => $infoTraducida['nombre'],
                'descripcion' => $infoTraducida['descripcion'],
                'icono_url' => $logro->icono_url,
                'rareza' => $logro->rareza,
                'requisito_tipo' => $logro->requisito_tipo,
                'requisito_cantidad' => $logro->requisito_cantidad,
                'desbloqueado' => $desbloqueado,
                'fecha_obtencion' => $desbloqueado ? $userLogros->get($logro->id)->fecha_desbloqueo : null,
            ];
        });

        return [
            'total_disponibles' => $todosLosLogros->count(),
            'total_obtenidos' => $userLogros->count(),
            'progreso_texto' => $userLogros->count() . '/' . $todosLosLogros->count(),
            'lista' => $lista
        ];
    }
}
