<?php

namespace App\Actions;

use App\Models\Tema;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Exception;

class ComprarTemaAction
{
    /**
     * Ejecuta la compra de un tema visual usando monedas del usuario.
     * 
     * @param Usuario $usuario
     * @param Tema $tema
     * @throws Exception Si el usuario ya tiene el tema o no tiene saldo suficiente.
     */
    public function execute(Usuario $usuario, Tema $tema): void
    {
        DB::transaction(function () use ($usuario, $tema) {
            // Volvemos a obtener el usuario con bloqueo de fila (LOCK FOR UPDATE)
            // para evitar condiciones de carrera en el saldo de monedas.
            $usuario = Usuario::where('id', $usuario->id)->lockForUpdate()->first();

            // Verificar si el usuario ya posee el tema
            if ($usuario->temas()->where('tema_id', $tema->id)->exists()) {
                throw new Exception('Ya posees este tema.');
            }

            // Verificar si tiene saldo suficiente
            if ($usuario->monedas < $tema->precio) {
                throw new Exception('No tienes suficientes monedas.');
            }

            // Deducir el coste del tema
            $usuario->monedas -= $tema->precio;
            $usuario->save();

            // Vincular el tema al usuario con la fecha de compra
            $usuario->temas()->attach($tema->id, ['comprado_at' => now()]);
        });
    }
}
