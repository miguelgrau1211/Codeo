<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsuarioDesactivado extends Model
{
    protected $table = 'usuarios_desactivados';

    protected $fillable = [
        'usuario_id_original',
        'nickname',
        'email',
        'nivel_alcanzado',
        'motivo',
        'fecha_desactivacion'
    ];
}