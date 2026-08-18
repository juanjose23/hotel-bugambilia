<?php

declare(strict_types=1);

namespace App\Http\Controllers\Clientes;

use App\Http\Controllers\Controller;
use App\Interactors\Landing\ObtenerReservasClienteLanding;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class CuentaClienteController extends Controller
{
    public function show(Request $request, ObtenerReservasClienteLanding $interactor): Response
    {
        $reservas = $interactor->ejecutar();

        return Inertia::render('portal/CuentaCliente', [
            'reservas' => $reservas,
        ]);
    }
}
