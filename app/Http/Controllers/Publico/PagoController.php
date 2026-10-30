<?php

declare(strict_types=1);

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Interactors\Publico\ObtenerPagoPublico;
use App\Repository\Models\Reservas\Reserva;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class PagoController extends Controller
{
    public function __invoke(ObtenerPagoPublico $pago): Response
    {
        return Inertia::render('pago/Pago', $pago->sinReserva());
    }

    public function reserva(Request $request, Reserva $reserva, ObtenerPagoPublico $pago): Response
    {
        $usuario = $request->user();
        $clienteId = $usuario?->persona?->cliente?->id;
        $esDuenio = $clienteId !== null && $clienteId === $reserva->cliente_id;
        $esAdmin = $usuario?->hasAnyRole(['admin', 'super_admin', 'recepcionista']) === true;
        $codigoValido = $request->query('codigo') === $reserva->codigo_reserva;

        abort_unless($codigoValido || $esDuenio || $esAdmin, 404);

        return Inertia::render('pago/Pago', $pago->paraReserva($reserva));
    }
}
