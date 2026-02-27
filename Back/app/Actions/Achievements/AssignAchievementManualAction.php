<?php

namespace App\Actions\Achievements;

use App\Models\UsuarioLogro;
use Illuminate\Support\Facades\Auth;

/**
 * Acción para asignar manualmente un logro a un usuario.
 */
class AssignAchievementManualAction
{
    /**
     * @param int $userId
     * @param int $logroId
     * @return UsuarioLogro
     * @throws \Exception
     */
    public function execute(int $userId, int $logroId): UsuarioLogro
    {
        // Verificar si ya lo tiene
        $existe = UsuarioLogro::where('usuario_id', $userId)
            ->where('logro_id', $logroId)
            ->first();

        if ($existe) {
            throw new \Exception('El usuario ya posee este logro.');
        }

        return UsuarioLogro::create([
            'usuario_id' => $userId,
            'logro_id' => $logroId,
            'fecha_desbloqueo' => now(),
        ]);
    }
}
