<?php

declare(strict_types=1);

namespace App\Http\Controllers\Restaurante;

use App\Http\Controllers\Controller;
use App\Http\Requests\Restaurante\ConfirmarPagoStripePedidoRequest;
use App\Interactors\Landing\ConfirmarPagoStripePedidoLanding;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

final class ConfirmarPagoStripePedidoController extends Controller
{
    public function __invoke(
        ConfirmarPagoStripePedidoRequest $request,
        ConfirmarPagoStripePedidoLanding $interactor,
    ): JsonResponse {
        /** @var array{pedido_id: int, payment_intent_id: string} $validated */
        $validated = $request->validated();

        try {
            $resultado = $interactor->ejecutar(
                pedidoId: (int) $validated['pedido_id'],
                paymentIntentId: (string) $validated['payment_intent_id'],
            );

            return response()->json($resultado);
        } catch (DomainException $e) {
            throw ValidationException::withMessages([
                'payment_intent_id' => [$e->getMessage()],
            ]);
        }
    }
}
