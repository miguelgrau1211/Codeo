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

    /**
     * Accesor para la imagen de previsualización.
     */
    public function getPreviewImgAttribute($value)
    {
        if (!$value) return null;
        
        // Si es una URL absoluta externa, la dejamos
        if (filter_var($value, FILTER_VALIDATE_URL) && !str_contains($value, 'localhost')) {
            return $value;
        }

        // Devolvemos ruta relativa al servidor
        $cleanPath = ltrim($value, '/');
        // Quitar prefijos si existen para normalizar
        if (str_starts_with($cleanPath, 'storage/')) $cleanPath = substr($cleanPath, 8);
        if (str_starts_with($cleanPath, 'assets/')) return '/' . $cleanPath;

        return '/storage/' . ltrim($cleanPath, '/');
    }
}
