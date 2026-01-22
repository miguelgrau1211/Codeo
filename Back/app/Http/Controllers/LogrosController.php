<?php

namespace App\Http\Controllers;

use App\Models\Logros;
use Illuminate\Http\Request;

class LogrosController
{
    public function index(){
        $logros = Logros::all();
        return response()->json($logros, 200);
    }

    public function show($id){
        $logro = Logros::findOrFail($id);
        return response()->json($logro, 200);
    }

    public function store(Request $request){
        $validatedData = $request->validate([
            'nombre' => 'required|string',
            'descripcion' => 'required|string',
            'icono_url' => 'required|string',
            'requisito_tipo' => 'required|string',
            'requisito_cantidad' => 'required|integer',
        ]);

        $logro = Logros::create($validatedData);

        return response()->json([
            'message' => 'Logro creado exitosamente',
            'data' => $logro
        ], 201);
    }

    public function update(Request $request, $id){
        $logro = Logros::findOrFail($id);

        $validatedData = $request->validate([
            'nombre' => 'required|string',
            'descripcion' => 'required|string',
            'icono_url' => 'required|string',
            'requisito_tipo' => 'required|string',
            'requisito_cantidad' => 'required|integer',
        ]);

        $logro->update($validatedData);

        return response()->json([
            'message' => 'Logro actualizado exitosamente',
            'data' => $logro
        ], 200);
    }

    public function destroy($id){
        $logro = Logros::findOrFail($id);
        $logro->delete();

        return response()->json([
            'message' => 'Logro eliminado exitosamente'
        ], 200);
    }

    
}
