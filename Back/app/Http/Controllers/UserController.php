<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Muestra una lista de todos los usuarios.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Obtiene todos los registros de la tabla 'usuarios'
        $usuarios = Usuario::all();
        
        // Devuelve los usuarios en formato JSON con un código de estado 200 (OK)
        return response()->json($usuarios, 200);
    }

    /**
     * Almacena un nuevo usuario en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Valida los datos entrantes de la petición basándose en el modelo Usuario
        $validatedData = $request->validate([
            'nickname' => 'required|string|max:50|unique:usuarios',
            'nombre' => 'nullable|string|max:255',
            'apellidos' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios',
            'password' => 'required|string|min:8',
            'avatar_url' => 'nullable|string|url',
            'terminos_aceptados' => 'required|boolean|accepted', // Debe ser true
        ]);

        // Crea una nueva instancia de Usuario con los datos validados
        $usuario = Usuario::create([
            'nickname' => $validatedData['nickname'],
            'nombre' => $validatedData['nombre'] ?? null,
            'apellidos' => $validatedData['apellidos'] ?? null,
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']), // Encriptar contraseña
            'avatar_url' => $validatedData['avatar_url'] ?? null,
            'terminos_aceptados' => $validatedData['terminos_aceptados'],
            // Los campos como monedas, nivel_global, etc., tienen valores por defecto en la BD
        ]);

        // Devuelve el usuario creado y un mensaje de éxito con código 201 (Created)
        return response()->json([
            'message' => 'Usuario creado exitosamente',
            'data' => $usuario
        ], 201);
    }

    /**
     * Muestra un usuario específico.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // Busca el usuario por su ID
        $usuario = Usuario::findOrFail($id);

        // Devuelve los datos del usuario encontrado
        return response()->json($usuario, 200);
    }

    /**
     * Actualiza un usuario existente en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Busca el usuario a actualizar
        $usuario = Usuario::findOrFail($id);

        // Valida los datos entrantes. permitiendo actualización parcial
        $validatedData = $request->validate([
            'nickname' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('usuarios')->ignore($usuario->id),
            ],
            'nombre' => 'nullable|string|max:255',
            'apellidos' => 'nullable|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('usuarios')->ignore($usuario->id),
            ],
            'password' => 'sometimes|required|string|min:8',
            'avatar_url' => 'nullable|string|url',
            'preferencias' => 'nullable|json', // Si se envían preferencias
        ]);

        // Si se envió una contraseña, la encriptamos antes de actualizar
        if (isset($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }

        // Actualiza el usuario con los datos validados
        $usuario->update($validatedData);

        // Devuelve el usuario actualizado y un mensaje
        return response()->json([
            'message' => 'Usuario actualizado exitosamente',
            'data' => $usuario
        ], 200);
    }

    /**
     * Elimina un usuario de la base de datos.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Busca el usuario o falla si no existe
        $usuario = Usuario::findOrFail($id);

        // Elimina el registro de la base de datos
        $usuario->delete();

        // Devuelve mensaje de éxito
        return response()->json([
            'message' => 'Usuario eliminado exitosamente'
        ], 200);
    }
}
