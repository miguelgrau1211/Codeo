<?php

namespace App\DTOs\User;

/**
 * DTO para la creación de un nuevo usuario.
 * Proporciona tipado estricto y seguridad en el transporte de datos.
 */
readonly class UserRegistrationData
{
    public function __construct(
        public string $nickname,
        public string $email,
        public string $password,
        public bool $terminosAceptados,
        public ?string $nombre = null,
        public ?string $apellidos = null,
        public ?string $avatarUrl = null,
    ) {
    }

    /**
     * Crea una instancia desde un array validado (Request).
     */
    public static function fromArray(array $data): self
    {
        return new self(
            nickname: $data['nickname'],
            email: $data['email'],
            password: $data['password'],
            terminosAceptados: (bool) $data['terminos_aceptados'],
            nombre: $data['nombre'] ?? null,
            apellidos: $data['apellidos'] ?? null,
            avatarUrl: $data['avatar_url'] ?? null,
        );
    }
}
