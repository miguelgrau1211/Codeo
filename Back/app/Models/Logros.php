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
        'requisito_operador',
        'requisito_cantidad',
    ];

    protected $casts = [
        'requisito_cantidad' => 'integer',
    ];

    /**
     * Accesor para la URL del icono.
     * Asegura que siempre devuelva una URL absoluta válida.
     */
    public function getIconoUrlAttribute($value)
    {
        if (!$value) return null;
        
        // Si ya es una URL absoluta (http/https), la devolvemos tal cual
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            // Pero si es de localhost (error antiguo), intentamos corregirla dinámicamente
            if (str_contains($value, 'localhost')) {
                return str_replace('http://localhost', config('app.url'), $value);
            }
            return $value;
        }

        // Si es una ruta relativa (ej: logros/medalla.png), generamos la URL pública
        return \Illuminate\Support\Facades\Storage::url($value);
    }
}
