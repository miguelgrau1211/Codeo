<?php

namespace App\Actions;

use App\Models\Tema;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Exception;

class ComprarTemaAction
{
    /**
     * @param Usuario $usuario
     * @param Tema $tema
     * @return void
     * @throws Exception
     */
    public function execute(Usuario $usuario, Tema $tema): void
    {
        DB::transaction(function () use ($usuario, $tema) {
            // Refetch user with lock for update to prevent race conditions as per rules
            $usuario = Usuario::where('id', $usuario->id)->lockForUpdate()->first();

            // Check if user already has the theme
            if ($usuario->temas()->where('tema_id', $tema->id)->exists()) {
                throw new Exception('Ya posees este tema.');
            }

            // Check if user has enough coins
            if ($usuario->monedas < $tema->precio) {
                throw new Exception('No tienes suficientes monedas.');
            }

            // Deduct coins
            $usuario->monedas -= $tema->precio;
            $usuario->save();

            // Attach theme
            $usuario->temas()->attach($tema->id, ['comprado_at' => now()]);
        });
    }
}
