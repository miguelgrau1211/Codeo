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
        
        // Si ya es una URL absoluta de internet (no localhost), la dejamos
        if (filter_var($value, FILTER_VALIDATE_URL) && !str_contains($value, 'localhost')) {
            return $value;
        }

        // Para rutas relativas o localhost, devolvemos la ruta absoluta al servidor (/storage/...)
        // Limpiamos posibles restos de 'storage/' en el path para no duplicar
        $cleanPath = ltrim($value, '/');
        if (str_starts_with($cleanPath, 'storage/')) {
            $cleanPath = substr($cleanPath, 8);
        }
        // Si venía de una URL de localhost, nos quedamos solo con lo que va después de /storage/
        if (str_contains($cleanPath, 'localhost')) {
            $parts = explode('/storage/', $cleanPath);
            $cleanPath = end($parts);
        }

        return '/storage/' . ltrim($cleanPath, '/');
    }
}
