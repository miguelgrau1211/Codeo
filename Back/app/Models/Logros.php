<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logros extends Model
{
    protected $table = 'logros';

    protected $fillable = [
        'id',
        'nombre',
        'descripcion',
        'icono_url',
        'rareza',
        'requisito_tipo',
        'requisito_cantidad',
    ];

    protected $casts = [
        'requisito_cantidad' => 'integer',
    ];
}
