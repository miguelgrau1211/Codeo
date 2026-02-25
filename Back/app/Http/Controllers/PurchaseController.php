<?php

namespace App\Http\Controllers;

use App\Actions\ProcessPurchaseAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    /**
     * Devuelve el estado premium del usuario + la clave pública de Stripe.
     * El frontend necesita la publishable key para inicializar Stripe.js.
     */
    public function getBattlePassStatus()
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        return response()->json([
            'is_premium' => (bool) $usuario->es_premium,
            'premium_since' => $usuario->premium_since,
            'price' => ProcessPurchaseAction::BATTLE_PASS_PRICE_DISPLAY,
            'currency' => ProcessPurchaseAction::CURRENCY,
            'stripe_publishable_key' => config('services.stripe.publishable_key'),
        ], 200);
    }

    /**
     * PASO 1: Crea un PaymentIntent en Stripe.
     * 
     * El frontend llama a este endpoint para obtener el client_secret,
     * que luego usa con Stripe.js para confirmar el pago de forma segura.
     */
    public function createPaymentIntent(ProcessPurchaseAction $action)
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $result = $action->createPaymentIntent($usuario);
        $statusCode = $result['success'] ? 200 : 422;

        return response()->json($result, $statusCode);
    }

    /**
     * PASO 2: Confirma que el pago fue exitoso y activa premium.
     * 
     * El frontend llama a este endpoint DESPUÉS de que Stripe.js
     * confirme el pago en el cliente.
     */
    public function confirmPayment(Request $request, ProcessPurchaseAction $action)
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $request->validate([
            'payment_intent_id' => 'required|string|starts_with:pi_',
        ]);

        $result = $action->confirmPayment($usuario, $request->input('payment_intent_id'));
        $statusCode = $result['success'] ? 200 : 422;

        return response()->json($result, $statusCode);
    }
}
