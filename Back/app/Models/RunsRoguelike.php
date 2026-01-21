<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    // Relación: Un run pertenece a un usuario
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    protected $casts = [
        'data_partida' => 'array',
    ];

}
   
