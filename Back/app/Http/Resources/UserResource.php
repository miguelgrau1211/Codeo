<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $nickname
 * @property string $email
 * @property string|null $nombre
 * @property string|null $apellidos
 * @property string|null $avatar_url
 * @property int $nivel_global
 * @property int $monedas
 * @property int $exp_total
 */
class UserResource extends JsonResource
{
    /**
     * Transforma el recurso en un array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nickname' => $this->nickname,
            'email' => $this->email,
            'profile' => [
                'nombre' => $this->nombre,
                'apellidos' => $this->apellidos,
                'avatar_url' => $this->avatar_url,
            ],
            'stats' => [
                'nivel_global' => $this->nivel_global,
                'monedas' => $this->monedas,
                'exp_total' => $this->exp_total,
            ],
            'is_admin' => (bool) $this->es_admin,
            'is_premium' => (bool) $this->es_premium,
        ];
    }
}
