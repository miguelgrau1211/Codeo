<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Usuario;
use App\Models\NivelesHistoria;

/**
 * Representa la relación entre un usuario y un nivel de historia específico.
 * Indica si el nivel ha sido superado y almacena la solución enviada.
 */
class ProgresoHistoria extends Model
{
    protected $table = 'usuario_progreso_historia';

    protected $fillable = [
        'usuario_id',
        'nivel_id',
        'completado',
        'codigo_solucion_usuario'
    ];

    /**
     * Obtiene el usuario propietario de este registro de progreso.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Obtiene los detalles del nivel asociado a este progreso.
     */
    public function nivel(): BelongsTo
    {
        return $this->belongsTo(NivelesHistoria::class, 'nivel_id');
    }
}