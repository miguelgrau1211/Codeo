<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Representa una sesión o "Run" de juego en el modo Roguelike.
 * Almacena el estado volátil que luego se sincroniza con el perfil global del usuario.
 */
class RunsRoguelike extends Model
{
    protected $table = 'runs_roguelike';

    protected $fillable = [
        'id',
        'usuario_id',
        'vidas_restantes',
        'niveles_superados',
        'monedas_obtenidas',
        'estado',
        'data_partida'
    ];

    /**
     * Relación: Un run pertenece a un usuario concreto.
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    /**
     * Atributos que deben convertirse a tipos nativos automáticamente.
     */
    protected $casts = [
        'data_partida' => 'array',
    ];
}

