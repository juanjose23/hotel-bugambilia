<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Voucher\GenerarVoucherPDF;
use App\Http\Requests\Reservas\CrearReservaRequest;
use App\Interactors\Reservas\Gestion\CancelarReserva;
use App\Interactors\Reservas\Gestion\CrearReserva;
use App\Repository\Models\Reservas\Reserva;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ReservaController extends Controller
{
    public function __construct(
        private readonly CrearReserva $crearReserva,
        private readonly CancelarReserva $cancelarReserva,
    ) {}

    public function crear(CrearReservaRequest $request): RedirectResponse
    {
        $datos = $request->validated();
        $datos['cliente_id'] = $request->user()?->persona?->cliente?->id;
        $servicios = $datos['servicios_adicionales'] ?? [];
        $espacios = $datos['espacios_adicionales'] ?? [];

        if (! is_array($servicios)) {
            $servicios = [];
        }

        if (! is_array($espacios)) {
            $espacios = [];
        }

        try {
            $reserva = $this->crearReserva->ejecutar($datos, $servicios, $espacios);

            $destino = $request->user() !== null
                ? route('mis-reservas', ['codigo' => $reserva->codigo_reserva])
                : route('home');

            return redirect($destino)
                ->with('exito', "¡Reserva realizada con éxito! Su código de reserva es: {$reserva->codigo_reserva}");
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['error' => $exception->getMessage()]);
        }
    }

    public function cancelar(Request $request, Reserva $reserva): RedirectResponse
    {
        $this->authorize('cancel', $reserva);

        try {
            $this->cancelarReserva->ejecutar($reserva, $request->user()?->id);
        } catch (DomainException $exception) {
            return back()->withErrors(['error' => $exception->getMessage()]);
        }

        return back()->with('exito', 'La reserva ha sido cancelada correctamente.');
    }

    public function voucher(Request $request, Reserva $reserva, GenerarVoucherPDF $action): StreamedResponse
    {
        $this->authorize('viewVoucher', $reserva);

        return $action->ejecutar($reserva);
    }
}
