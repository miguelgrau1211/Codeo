<?php

namespace App\Http\Controllers;

use App\Models\NivelesHistoria;
use App\Models\NivelRoguelike;
use Illuminate\Http\Request;

class EjecutarCodigo extends Controller
{
    public function ejecutarCodigo(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string',
            'tipo' => 'required|in:historia,roguelike',
            'nivel_id' => 'required|integer'
        ]);

        $codigoUsuario = $request->codigo;
        $tipo = $request->tipo;
        $nivelId = $request->nivel_id;
        $resultadoEsperado = null;

        // 1. Obtener el resultado esperado de la Base de Datos según el tipo de nivel
        if ($tipo === 'historia') {
            $nivel = NivelesHistoria::find($nivelId);
            if (!$nivel) {
                return response()->json(['message' => 'Nivel de historia no encontrado'], 404);
            }
            // En historia, comparamos con 'solucion_esperada' (asumiendo que es el valor de retorno).
            // OJO: 'solucion_esperada' en la BD podría ser el código que da la solución o el valor exacto.
            // Asumiremos que es el valor exacto que debe devolver el código.
            $resultadoEsperado = $nivel->solucion_esperada;

        } else { // Roguelike
            $nivel = NivelRoguelike::find($nivelId);
            if (!$nivel) {
                return response()->json(['message' => 'Nivel roguelike no encontrado'], 404);
            }
            // En Roguelike, tenemos 'codigo_validador' que podría ser el resultado esperado
            $resultadoEsperado = $nivel->codigo_validador;
        }

        // 2. Ejecutar el código del usuario (¡PELIGRO!: eval() es inseguro en producción)
        // Se recomienda usar sandboxes como Judge0 o Dockercontainers aislados.
        try {
            // Un eval muy básico. Capturamos la salida.
            // Si el código debe hacer `return ...;` eval lo devuelve.
            // Si hace `echo`, necesitamos output buffering.
            
            ob_start();
            $resultadoUsuario = eval($codigoUsuario);
            $output = ob_get_clean();

            // Si eval retorna null pero hubo output (echo), usamos el output.
            if ($resultadoUsuario === null && !empty($output)) {
                $resultadoUsuario = trim($output);
            }
            
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error de ejecución',
                'error' => $e->getMessage()
            ], 200); // 200 para que el front lo muestre amistosamente
        }

        // 3. Comparar resultados
        // Nota: Esto es una comparación laxa. Depende de cómo guardes 'solucion_esperada'.
        // Si 'solucion_esperada' es "15" y el usuario devuelve entero 15, esto funciona.
        if ($resultadoUsuario == $resultadoEsperado) {
            return response()->json([
                'correcto' => true,
                'message' => '¡Código Correcto!',
                'resultado_obtenido' => $resultadoUsuario
            ], 200);
        } else {
            return response()->json([
                'correcto' => false,
                'message' => 'Código Incorrecto',
                'esperado' => $resultadoEsperado,
                'obtenido' => $resultadoUsuario
            ], 200);
        }
    }
}