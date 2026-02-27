<?php

namespace App\Http\Controllers;

use App\Actions\Shop\ProcessPurchaseAction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Controlador para la gestión de compras y suscripciones premium (Stripe).
 */
class PurchaseController extends Controller
{
    /**
     * Devuelve el estado premium del usuario y la clave pública de Stripe.
     */
    public function getBattlePassStatus(): JsonResponse
    {
        $usuario = Auth::user();

        return response()->json([
            'is_premium' => (bool) $usuario->es_premium,
            'premium_since' => $usuario->premium_since,
            'price' => ProcessPurchaseAction::BATTLE_PASS_PRICE_DISPLAY,
            'currency' => ProcessPurchaseAction::CURRENCY,
            'stripe_publishable_key' => config('services.stripe.publishable_key'),
        ]);
    }

    /**
     * PASO 1: Crea un PaymentIntent en Stripe.
     */
    public function createPaymentIntent(ProcessPurchaseAction $action): JsonResponse
    {
        $result = $action->createPaymentIntent(Auth::user());
        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * PASO 2: Confirma que el pago fue exitoso y activa premium.
     */
    public function confirmPayment(Request $request, ProcessPurchaseAction $action): JsonResponse
    {
        $request->validate([
            'payment_intent_id' => 'required|string|starts_with:pi_',
        ]);

        $result = $action->confirmPayment(Auth::user(), $request->input('payment_intent_id'));
        return response()->json($result, $result['success'] ? 200 : 422);
    }
}