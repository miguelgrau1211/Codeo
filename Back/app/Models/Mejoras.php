<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mejoras extends Model
{
    protected $table = 'mejoras';

    protected $fillable = [
        'id',
        'nombre',
        'descripcion',
        'tipo',
        'precio_monedas',
    ];

    protected $casts = [
        'precio_monedas' => 'integer',
    ];
}
