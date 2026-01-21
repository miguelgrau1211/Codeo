<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsuarioLogro extends Model
{
    protected $table = 'usuario_logros_';
    public $incrementing = false;
    protected $primaryKey = ['usuario_id', 'logro_id'];

    protected $fillable = [
        'usuario_id',
        'logro_id',
        'fecha_desbloqueo'
    ];

    /**
     * Relación con el Usuario
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Relación con el Logro
     */
    public function logro(): BelongsTo
    {
        return $this->belongsTo(Logro::class, 'logro_id');
    }
}