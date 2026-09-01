<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reservas;

use App\Http\Controllers\Controller;
use App\Interactors\Landing\AutenticarClientePorCodigoReserva;
use App\Interactors\Landing\ObtenerReservasClienteLanding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class DetalleReservaPortalController extends Controller
{
    public function show(
        int $id,
        Request $request,
        ObtenerReservasClienteLanding $interactor,
        AutenticarClientePorCodigoReserva $autenticarPorCodigo,
    ): Response {
        $codigo = $request->string('codigo')->toString();

        // Si el cliente no está logueado pero ingresó un código de reserva válido,
        // autenticarlo y crear su sesión de usuario normal.
        if (Auth::guest() && $codigo !== '') {
            $user = $autenticarPorCodigo->ejecutar($codigo);
            if ($user !== null) {
                $request->session()->regenerate();
            }
        }

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
