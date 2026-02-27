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
        
        $url = $value;
        // Si es una ruta relativa, generar la URL usando Storage
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $url = \Illuminate\Support\Facades\Storage::url($value);
        }

        // Si por algún motivo (configuración de Laravel o base de datos) la URL contiene localhost,
        // la forzamos a usar la URL configurada en el servidor.
        if (str_contains($url, 'localhost')) {
            $appUrl = rtrim(config('app.url'), '/');
            return str_replace(['http://localhost:8000', 'http://localhost'], $appUrl, $url);
        }

        return $url;
    }
}
