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
        
        $url = $value;
        // Si no es URL absoluta, usar asset() para generarla
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $url = asset($value);
        }

        // Forzar reemplazo de localhost si aparece
        if (str_contains($url, 'localhost')) {
            $appUrl = rtrim(config('app.url'), '/');
            return str_replace(['http://localhost:8000', 'http://localhost'], $appUrl, $url);
        }

        return $url;
    }
}
