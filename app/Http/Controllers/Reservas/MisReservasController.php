<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reservas;

use App\Http\Controllers\Controller;
use App\Interactors\Landing\ObtenerReservasClienteLanding;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class MisReservasController extends Controller
{
    public function __invoke(Request $request, ObtenerReservasClienteLanding $interactor): Response
    {
        $codigo = $request->string('codigo')->toString();

        return Inertia::render('reservas/MisReservas', [
            'reservas' => $interactor->ejecutar($codigo !== '' ? $codigo : null),
            'codigoBusqueda' => $codigo,
        ]);
    }
}
