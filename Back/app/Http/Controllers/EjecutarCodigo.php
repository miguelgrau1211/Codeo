<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EjecutarCodigo extends Controller
{
    public function ejecutarCodigo(Request $request)
    {
        $codigo = $request->codigo;
        $resultado = eval($codigo);
        return response()->json($resultado, 200);
    }
}