<?php

declare(strict_types=1);

namespace App\Http\Controllers\WebServices\Reservas;

use App\BusinessLogic\Reservas\Data\CancelarReservaHabitacionData;
use App\Http\Controllers\Controller;
use App\Interactors\Reservas\Gestion\CancelarReservaPublica;
use App\Repository\Models\Reservas\Reserva;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CancelarReservaWebServiceController extends Controller
{
    public function __invoke(
        Request $request,
        Reserva $reserva,
        CancelarReservaPublica $cancelarReserva,
    ): JsonResponse {
        $this->authorize('cancel', $reserva);

        $datos = $request->validate([
            'motivo' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $resultado = $cancelarReserva->ejecutar(new CancelarReservaHabitacionData(
                reservaId: $reserva->id,
                motivo: is_string($datos['motivo'] ?? null) ? $datos['motivo'] : 'Reserva cancelada',
                usuarioId: $request->user()?->id,
            ));
        } catch (DomainException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $reembolsoPendiente = $resultado['reembolso_pendiente_administracion'];

        return response()->json([
            'message' => $reembolsoPendiente
                ? 'Tu reserva fue cancelada, pero no pudimos procesar el reembolso automaticamente. Por favor contacta a la administracion para resolver tu reembolso.'
                : 'Tu reserva fue cancelada correctamente.',
            'codigo_reserva' => $resultado['reserva']->codigo_reserva,
            'estado' => $resultado['reserva']->estado->value,
            'reembolso' => [
                'pendiente_administracion' => $reembolsoPendiente,
                'intentos_stripe' => $resultado['intentos_stripe'],
            ],
        ]);
    }
}
