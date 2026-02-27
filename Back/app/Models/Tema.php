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
        if (filter_var($value, FILTER_VALIDATE_URL)) return $value;

        // Si es una ruta de assets o almacenamiento, asegurar que sea pública
        return asset($value);
    }
}
