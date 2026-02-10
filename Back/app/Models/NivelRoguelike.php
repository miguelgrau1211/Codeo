<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NivelRoguelike extends Model
{
    use HasFactory;

    protected $table = 'niveles_roguelike';

    protected $fillable = [
        'dificultad',
        'titulo',
        'descripcion',
        'test_cases',
        'recompensa_monedas'
    ];

    protected $casts = [
        'recompensa_monedas' => 'integer',
        'test_cases' => 'array',
        
    ];
}