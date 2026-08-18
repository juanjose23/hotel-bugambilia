<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reservas;

use App\Http\Controllers\Controller;
use App\Interactors\Landing\ObtenerReservasClienteLanding;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class DetalleReservaPortalController extends Controller
{
    public function show(int $id, Request $request, ObtenerReservasClienteLanding $interactor): Response
    {
        $codigo = $request->string('codigo')->toString();
        $reservas = $interactor->ejecutar($codigo !== '' ? $codigo : null);

        $reservaSeleccionada = collect($reservas)->firstWhere('id', $id);

        if ($reservaSeleccionada === null && $reservas !== []) {
            $reservaSeleccionada = $reservas[0];
        }

        return Inertia::render('reservas/DetalleReservaPortal', [
            'reserva' => $reservaSeleccionada,
            'reservas' => $reservas,
        ]);
    }
}
