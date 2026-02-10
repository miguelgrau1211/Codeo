<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NivelRoguelikeDesactivado extends Model
{
    use HasFactory;

    protected $table = 'niveles_roguelike_desactivados';

    protected $fillable = [
        'nivel_id_original',
        'dificultad',
        'titulo',
        'descripcion',
        'test_cases',
        'recompensa_monedas',
        'motivo',
        'fecha_desactivacion'
    ];

    protected $casts = [
        'test_cases' => 'array',
        'fecha_desactivacion' => 'datetime',
        'recompensa_monedas' => 'integer'
    ];
}
