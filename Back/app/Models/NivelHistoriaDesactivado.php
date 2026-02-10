<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NivelHistoriaDesactivado extends Model
{
    use HasFactory;

    protected $table = 'niveles_historia_desactivados';

    protected $fillable = [
        'nivel_id_original',
        'orden',
        'titulo',
        'descripcion',
        'contenido_teorico',
        'codigo_inicial',
        'test_cases',
        'recompensa_exp',
        'recompensa_monedas',
        'motivo',
        'fecha_desactivacion'
    ];

    protected $casts = [
        'test_cases' => 'array',
        'fecha_desactivacion' => 'datetime',
        'orden' => 'integer',
        'recompensa_exp' => 'integer',
        'recompensa_monedas' => 'integer'
    ];
}
