<?php

declare(strict_types=1);

namespace App\Http\Controllers\Restaurante;

use App\BusinessLogic\Restaurante\Mesas\VerificarRestauranteActivo;
use App\Http\Controllers\Controller;
use App\Interactors\Restaurante\ObtenerRestaurante;
use Inertia\Inertia;
use Inertia\Response;

final class RestauranteController extends Controller
{
    public function __invoke(
        ObtenerRestaurante $interactor,
        VerificarRestauranteActivo $verificarRestauranteActivo,
    ): Response {
        if (! $verificarRestauranteActivo->estaHabilitadoWeb()) {
            abort(404, 'El restaurante no se encuentra disponible actualmente.');
        }

        $datos = $interactor->ejecutar();

        if ($datos['restaurante'] === null) {
            abort(404, 'El restaurante no se encuentra disponible actualmente.');
        }

        return Inertia::render('restaurante/Restaurante', $datos);
    }
}
