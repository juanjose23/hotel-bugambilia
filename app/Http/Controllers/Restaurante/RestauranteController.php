<?php

declare(strict_types=1);

namespace App\Http\Controllers\Restaurante;

use App\Http\Controllers\Controller;
use App\Interactors\Landing\ObtenerRestauranteLanding;
use Inertia\Inertia;
use Inertia\Response;

final class RestauranteController extends Controller
{
    public function __invoke(ObtenerRestauranteLanding $interactor): Response
    {
        return Inertia::render('restaurante/Restaurante', $interactor->ejecutar());
    }
}
