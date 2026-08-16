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
        abort_unless($request->query('codigo') === $reserva->codigo_reserva, 404);

        return Inertia::render('pago/Pago', $pago->paraReserva($reserva));
    }
}
