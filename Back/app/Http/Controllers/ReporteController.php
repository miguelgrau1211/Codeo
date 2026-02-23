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

    // Crea un reporte (desde el frontend)
    public function store(Request $request)
    {
        $usuarioId = auth()->id();

        // 🛡️ Anti-Spam: Limitar a 3 reportes pendientes por usuario
        $reportesPendientes = Reporte::where('usuario_id', $usuarioId)
            ->where('estado', 'pendiente')
            ->count();

        if ($reportesPendientes >= 3) {
            return response()->json([
                'message' => 'Tienes demasiados reportes pendientes (máximo 3). Por favor, espera a que los revisemos.'
            ], 429);
        }

        $validatedData = $request->validate([
            'email_contacto' => 'nullable|email|max:255',
            'tipo' => 'required|string|max:50',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'prioridad' => 'nullable|string|in:baja,media,alta,critica',
        ]);

        $reporte = Reporte::create([
            'usuario_id' => $usuarioId,
            'email_contacto' => $validatedData['email_contacto'] ?? auth()->user()?->email,
            'tipo' => $validatedData['tipo'],
            'titulo' => $validatedData['titulo'],
            'descripcion' => $validatedData['descripcion'],
            'prioridad' => $validatedData['prioridad'] ?? 'media',
            'estado' => 'pendiente',
        ]);

        return response()->json([
            'message' => 'Reporte creado correctamente. ¡Gracias por ayudarnos a mejorar!',
            'data' => $reporte
        ], 201);
    }

    //mostrar
    public function show($id)
    {
        $reporte = Reporte::with('usuario')->findOrFail($id);
        return response()->json($reporte, 200);
    }

    // Actualizar reporte (Solo Admin)
    public function update(Request $request, $id)
    {
        $reporte = Reporte::findOrFail($id);
        $anteriorEstado = $reporte->estado;

        $validatedData = $request->validate([
            'estado' => 'nullable|string|in:pendiente,en revision,solucionado,rechazado,spam',
            'prioridad' => 'nullable|string|in:baja,media,alta,critica',
        ]);

        $reporte->update($validatedData);

        $puntosXP = null;
        $mensajeExtra = "";

        // ✅ Recompensa: Solucionado
        if ($reporte->estado === 'solucionado' && $anteriorEstado !== 'solucionado') {
            $usuario = $reporte->usuario;
            if ($usuario) {
                $puntosXP = ($reporte->prioridad === 'critica' || $reporte->prioridad === 'alta') ? 100 : 50;
                $usuario->exp_total += $puntosXP;

                // Lógica subir nivel
                while ($usuario->exp_total >= ($usuario->nivel_global * 100)) {
                    $usuario->exp_total -= ($usuario->nivel_global * 100);
                    $usuario->nivel_global++;
                }
                $usuario->save();
                $mensajeExtra = " y usuario recompensado con $puntosXP XP";
            }
        }

        // ❌ Penalización: Spam
        if ($reporte->estado === 'spam' && $anteriorEstado !== 'spam') {
            $usuario = $reporte->usuario;
            if ($usuario) {
                $puntosPenalizacion = 100;
                // Restar XP sin bajar de 0
                $usuario->exp_total = max(0, $usuario->exp_total - $puntosPenalizacion);
                $usuario->save();
                $mensajeExtra = " y usuario penalizado con -$puntosPenalizacion XP";
            }
        }

        return response()->json([
            'message' => 'Reporte actualizado correctamente' . $mensajeExtra,
            'data' => $reporte
        ], 200);
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