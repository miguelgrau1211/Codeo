<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * El nombre de la tabla asociado al modelo.
     *
     * @var string
     */
    protected $table = 'usuarios';

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'nickname',
        'nombre',
        'apellidos',
        'email',
        'password',
        'avatar_url',
        'monedas',
        'nivel_global',
        'exp_total',
        'streak',
        'max_streak',
        'ultima_conexion',
        'ultimo_nivel_completado_at',
        'google_id',
        'google_token',
        'google_refresh_token',
        'preferencias',
        'terminos_aceptados',
        'es_admin',
        'es_premium',
        'premium_since',
        'tema_actual_id',
    ];

    /**
     * Los atributos que deben ocultarse para la serialización.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Los atributos que deben convertirse a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'ultima_conexion' => 'datetime',
        'preferencias' => 'array',
        'terminos_aceptados' => 'boolean',
        'monedas' => 'integer',
        'nivel_global' => 'integer',
        'exp_total' => 'integer',
        'streak' => 'integer',
        'max_streak' => 'integer',
        'es_admin' => 'boolean',
        'es_premium' => 'boolean',
        'premium_since' => 'datetime',
        'tema_actual_id' => 'integer',
        'ultimo_nivel_completado_at' => 'datetime',
    ];

    public function temas()
    {
        return $this->belongsToMany(Tema::class, 'usuario_tema')
            ->withPivot('comprado_at')
            ->withTimestamps();
    }

    public function temaActual()
    {
        return $this->belongsTo(Tema::class, 'tema_actual_id');
    }
}
