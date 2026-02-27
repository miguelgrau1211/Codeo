<?php

namespace App\Actions\User;

use App\Models\Usuario;
use App\Models\UsuarioDesactivado;
use Illuminate\Support\Facades\DB;

/**
 * Acción para reactivar a un usuario que estaba desactivado.
 */
class ReactivateUserAction
{
    /**
     * Reactiva al usuario moviéndolo de regreso a la tabla principal.
     */
    public function execute(int $usuarioId, ?string $newPassword = null): bool
    {
        $archivado = UsuarioDesactivado::where('usuario_id_original', $usuarioId)->firstOrFail();

        // Validar que el email o nickname no hayan sido usados
        $existe = Usuario::where('email', $archivado->email)
            ->orWhere('nickname', $archivado->nickname)
            ->exists();

        if ($existe) {
            throw new \Exception('El nickname o email ya están ocupados por un usuario activo');
        }

        return DB::transaction(function () use ($archivado, $newPassword) {
            Usuario::create([
                'id' => $archivado->usuario_id_original,
                'nickname' => $archivado->nickname,
                'nombre' => $archivado->nombre,
                'apellidos' => $archivado->apellidos,
                'email' => $archivado->email,
                'password' => bcrypt($newPassword ?? 'Codeo'),
                'nivel_global' => $archivado->nivel_alcanzado,
                'terminos_aceptados' => true
            ]);

            return $archivado->delete();
        });
    }
}
