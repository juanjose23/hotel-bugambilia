<?php

declare(strict_types=1);

namespace App\Http\Controllers\WebServices\Reservas;

use App\BusinessLogic\Reservas\Data\CancelarReservaHabitacionData;
use App\Http\Controllers\Controller;
use App\Http\Requests\WebServices\Reservas\CancelarReservaWebServiceRequest;
use App\Interactors\Reservas\Gestion\CancelarReserva;
use App\Repository\Models\Reservas\Reserva;
use DomainException;
use Illuminate\Http\JsonResponse;

final class CancelarReservaWebServiceController extends Controller
{
    public function __invoke(
        CancelarReservaWebServiceRequest $request,
        Reserva $reserva,
        CancelarReserva $cancelarReserva,
    ): JsonResponse {
        $this->authorize('cancel', $reserva);

        $datos = $request->validated();

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
