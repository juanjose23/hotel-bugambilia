<?php

declare(strict_types=1);

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Interactors\Home\ObtenerDatosHome;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class HomeController extends Controller
{
    public function __invoke(Request $request, ObtenerDatosHome $interactor): Response
    {
        $datos = $interactor->ejecutar();

        return Inertia::render('home/Home', $datos);
    }
}
