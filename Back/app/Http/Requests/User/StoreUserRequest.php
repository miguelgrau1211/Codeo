<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta petición.
     */
    public function authorize(): bool
    {
        return true; // En registro, cualquiera puede peticionar
    }

    /**
     * Reglas de validación para el registro.
     */
    public function rules(): array
    {
        return [
            'nickname' => 'required|string|max:50|unique:usuarios,nickname',
            'nombre' => 'nullable|string|max:255',
            'apellidos' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios,email',
            'password' => 'required|string|min:8',
            'avatar_url' => 'nullable|string|url',
            'terminos_aceptados' => 'required|boolean|accepted',
        ];
    }

    /**
     * Mensajes de error personalizados (Opcional, pero bueno para accesibilidad).
     */
    public function messages(): array
    {
        return [
            'nickname.unique' => 'Ese nombre de usuario ya está en uso.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'terminos_aceptados.accepted' => 'Debes aceptar los términos y condiciones.',
        ];
    }
}
