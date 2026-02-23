<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tema extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'css_variables',
        'preview_img',
    ];

    protected $casts = [
        'css_variables' => 'array',
        'precio' => 'integer',
    ];

    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'usuario_tema')
                    ->withPivot('comprado_at')
                    ->withTimestamps();
    }
}
