<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user') ?: auth()->id();

        return [
            'nickname' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('usuarios', 'nickname')->ignore($userId),
            ],
            'nombre' => 'nullable|string|max:255',
            'apellidos' => 'nullable|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('usuarios', 'email')->ignore($userId),
            ],
            'password' => 'sometimes|required|string|min:8',
            'avatar_url' => 'nullable|string|url',
            'preferencias' => 'nullable|array',
        ];
    }
}
