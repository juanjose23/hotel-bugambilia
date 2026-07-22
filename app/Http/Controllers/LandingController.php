<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Interactors\Landing\ObtenerDatosLanding;
use Inertia\Inertia;
use Inertia\Response;

final class LandingController extends Controller
{
    public function __invoke(ObtenerDatosLanding $interactor): Response
    {
        $datos = $interactor->ejecutar();

        return Inertia::render('Home', $datos);
    }
}
