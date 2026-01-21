<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    // Lista todos los reportes 
    public function index()
    {
        // reporte con los datos básicos del usuario
        $reportes = Reporte::with('usuario')->get();
        return response()->json($reportes, 200);
    }

    //desde el frontend.
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'usuario_id'  => 'required|exists:usuarios,id',
            'tipo'        => 'required|string|max:50',
            'titulo'      => 'required|string|max:255',
            'descripcion' => 'required|string',
        ]);

        $reporte = Reporte::create([
            'usuario_id'  => $validatedData['usuario_id'],
            'tipo'        => $validatedData['tipo'],
            'titulo'      => $validatedData['titulo'],
            'descripcion' => $validatedData['descripcion'],
            'estado'      => 'pendiente',
        ]);

        return response()->json([
            'message' => 'Reporte creado correctamente',
            'data'    => $reporte
        ], 201);
    }

    //mostrar
    public function show($id)
    {
        $reporte = Reporte::with('usuario')->findOrFail($id);
        return response()->json($reporte, 200);
    }

    //eliminar
    public function destroy($id)
    {
        $reporte = Reporte::findOrFail($id);
        $reporte->delete();

        return response()->json([
            'message' => 'Reporte eliminado correctamente'
        ], 200);
    }
}