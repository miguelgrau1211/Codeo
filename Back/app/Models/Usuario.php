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
        'nickname',
        'nombre',
        'apellidos',
        'email',
        'password',
        'avatar_url',
        'monedas',
        'nivel_global',
        'exp_total',
        'racha_dias',
        'ultima_conexion',
        'preferencias',
        'terminos_aceptados',
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
        'racha_dias' => 'integer',
    ];
}
