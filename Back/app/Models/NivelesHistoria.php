<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NivelesHistoria extends Model
{
    protected $table = 'niveles_historia';

    protected $fillable = [
        'id',
        'orden',
        'titulo',
        'descripcion',
        'contenido_teorico',
        'codigo_inicial',
        'solucion_esperada',
        'recompensa_exp',
        'recompensa_monedas'
    ];

    protected $casts = [
        'recompensa_exp' => 'integer',
        'recompensa_monedas' => 'integer',
    ];

    
}
