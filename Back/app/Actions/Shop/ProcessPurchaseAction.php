<?php

namespace App\Actions\Shop;

use App\Models\Usuario;
use App\Actions\GrantBattlePassRewardsAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;

/**
 * Procesa la compra del Pase de Batalla usando Stripe Payment Intents.
 * 
 * Flujo:
 * 1. Frontend recoge tarjeta con Stripe.js -> genera payment_method_id
 * 2. Backend crea un PaymentIntent con ese payment_method y lo confirma
 * 3. Si Stripe confirma el pago -> se activa premium
 * 4. Se logea toda la transacción en consola/logs
 */
class ProcessPurchaseAction
{
    /**
     * Precio del Pase de Batalla en céntimos (Stripe trabaja en la unidad más pequeña).
     * 9.99€ = 999 cents
     */
    public const BATTLE_PASS_PRICE_CENTS = 999;
    public const BATTLE_PASS_PRICE_DISPLAY = '9.99';
    public const CURRENCY = 'eur';

    /**
     * PASO 1: Crea un PaymentIntent en Stripe y devuelve el client_secret.
     * El client_secret se usa en el frontend para confirmar el pago con Stripe.js.
     */
    public function createPaymentIntent(Usuario $usuario): array
    {
        if ($usuario->es_premium) {
            return [
                'success' => false,
                'error' => 'already_premium',
                'message' => 'Ya tienes el Pase de Batalla activo.',
            ];
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $paymentIntent = PaymentIntent::create([
                'amount' => self::BATTLE_PASS_PRICE_CENTS,
                'currency' => self::CURRENCY,
                'metadata' => [
                    'user_id' => $usuario->id,
                    'product' => 'battle_pass',
                    'user_email' => $usuario->email,
                ],
                'description' => "Codeo Battle Pass - Usuario #{$usuario->id} ({$usuario->nickname})",
            ]);

            Log::channel('stderr')->info('🔵 [STRIPE] PaymentIntent creado', [
                'payment_intent_id' => $paymentIntent->id,
                'amount' => self::BATTLE_PASS_PRICE_DISPLAY . '€',
                'user_id' => $usuario->id,
                'user_nickname' => $usuario->nickname,
                'status' => $paymentIntent->status,
            ]);

            return [
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
                'amount' => self::BATTLE_PASS_PRICE_DISPLAY,
                'currency' => self::CURRENCY,
            ];
        } catch (ApiErrorException $e) {
            Log::channel('stderr')->error('🔴 [STRIPE] Error creando PaymentIntent', [
                'error' => $e->getMessage(),
                'user_id' => $usuario->id,
            ]);

            return [
                'success' => false,
                'error' => 'stripe_error',
                'message' => 'Error al conectar con la pasarela de pago: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * PASO 2: Confirma que el pago fue exitoso y activa premium.
     * Se ejecuta después de que Stripe.js confirme el pago en el frontend.
     */
    public function confirmPayment(Usuario $usuario, string $paymentIntentId): array
    {
        if ($usuario->es_premium) {
            return [
                'success' => false,
                'error' => 'already_premium',
                'message' => 'Ya tienes el Pase de Batalla activo.',
            ];
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            // Verificar el estado del PaymentIntent en Stripe
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

            // Verificar que el PaymentIntent pertenece a este usuario
            if (($paymentIntent->metadata->user_id ?? null) != $usuario->id) {
                Log::channel('stderr')->warning('🟡 [STRIPE] PaymentIntent no pertenece al usuario', [
                    'payment_intent_id' => $paymentIntentId,
                    'expected_user' => $usuario->id,
                    'actual_user' => $paymentIntent->metadata->user_id ?? 'N/A',
                ]);

                return [
                    'success' => false,
                    'error' => 'unauthorized',
                    'message' => 'Este pago no pertenece a tu cuenta.',
                ];
            }

            // Verificar que el pago fue exitoso
            if ($paymentIntent->status !== 'succeeded') {
                Log::channel('stderr')->warning('🟡 [STRIPE] PaymentIntent no completado', [
                    'payment_intent_id' => $paymentIntentId,
                    'status' => $paymentIntent->status,
                    'user_id' => $usuario->id,
                ]);

                return [
                    'success' => false,
                    'error' => 'payment_not_completed',
                    'message' => 'El pago no se ha completado. Estado: ' . $paymentIntent->status,
                ];
            }

            // ✅ Pago confirmado -> Activar Premium
            return DB::transaction(function () use ($usuario, $paymentIntent) {
                // Bloqueo de fila para evitar condiciones de carrera
                $usuario = Usuario::lockForUpdate()->find($usuario->id);

                $usuario->es_premium = true;
                $usuario->premium_since = now();
                $usuario->save();

                Log::channel('stderr')->info('🔵 [BATTLEPASS] Usuario actualizado a Premium', ['id' => $usuario->id]);

                // Otorgar recompensas pendientes del pase de batalla
                $battlePassRewards = (new GrantBattlePassRewardsAction())->execute($usuario);

                // === LOG EN CONSOLA ===
                Log::channel('stderr')->info('🔵 [BATTLEPASS] BattlePass purchased via Stripe', [
                    'payment_intent_id' => $paymentIntent->id,
                    'user_id' => $usuario->id,
                    'amount' => self::BATTLE_PASS_PRICE_DISPLAY,
                    'currency' => self::CURRENCY,
                    'rewards_granted' => count($battlePassRewards),
                ]);



                

                // También log en el archivo estándar
                Log::info('BattlePass purchased via Stripe', [
                    'payment_intent_id' => $paymentIntent->id,
                    'user_id' => $usuario->id,
                    'amount' => self::BATTLE_PASS_PRICE_DISPLAY,
                    'currency' => self::CURRENCY,
                    'rewards_granted' => count($battlePassRewards),
                ]);

                return [
                    'success' => true,
                    'message' => '¡Pase de Batalla activado con éxito!',
                    'payment_intent_id' => $paymentIntent->id,
                    'premium_since' => $usuario->premium_since->toIso8601String(),
                    'rewards_granted' => $battlePassRewards,
                ];
            });
        } catch (ApiErrorException $e) {
            Log::channel('stderr')->error('🔴 [STRIPE] Error confirmando pago', [
                'error' => $e->getMessage(),
                'payment_intent_id' => $paymentIntentId,
                'user_id' => $usuario->id,
            ]);

            return [
                'success' => false,
                'error' => 'stripe_error',
                'message' => 'Error al verificar el pago: ' . $e->getMessage(),
            ];
        }
    }
}
