<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Usuario;
use App\Models\NivelesHistoria;

class ProgresoHistoria extends Model
{
    protected $table = 'usuario_progreso_historia';

    protected $fillable = [
        'usuario_id',
        'nivel_id',
        'completado',
        'codigo_solucion_usuario'
    ];

    // Relación con el usuario
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    // Relación con el nivel
    public function nivel(): BelongsTo
    {
        return $this->belongsTo(NivelesHistoria::class, 'nivel_id');
    }
}