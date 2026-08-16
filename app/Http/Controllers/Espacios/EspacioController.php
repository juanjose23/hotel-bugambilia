<?php

declare(strict_types=1);

namespace App\Http\Controllers\Espacios;

use App\Http\Controllers\Controller;
use App\Interactors\Landing\ObtenerEspacioDetalleLanding;
use App\Interactors\Landing\ObtenerEspaciosLanding;
use App\Interactors\Landing\ObtenerOpcionesReservaLanding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class EspacioController extends Controller
{
    public function index(Request $request, ObtenerEspaciosLanding $interactor): Response
    {
        $tipo = $request->string('tipo')->toString();

        return Inertia::render('espacios/Espacios', [
            'espacios' => $interactor->ejecutar($tipo !== '' ? $tipo : null),
            'tipos' => $interactor->tiposDisponibles(),
            'tipoSeleccionado' => $tipo !== '' ? $tipo : 'TODOS',
        ]);
    }

    public function show(
        string|int $slug,
        ObtenerEspacioDetalleLanding $interactor,
        ObtenerOpcionesReservaLanding $opciones,
    ): Response|RedirectResponse {
        $detalle = $interactor->ejecutar($slug);
        $space = $detalle['space'];

        if (! empty($space['es_restaurante']) || (is_string($space['tipo'] ?? null) && strtolower($space['tipo']) === 'restaurante') || $slug === 'restaurante') {
            return to_route('restaurante');
        }

        $rawId = $space['id'] ?? null;
        $espacioId = is_numeric($rawId) ? (int) $rawId : 0;

        return Inertia::render('espacios/EspacioDetalle', [
            ...$detalle,
            'opcionesReserva' => $opciones->ejecutar($espacioId),
        ]);
    }

    public function mostrarReserva(
        string|int $slug,
        ObtenerEspacioDetalleLanding $interactor,
        ObtenerOpcionesReservaLanding $opciones,
    ): Response|RedirectResponse {
        $detalle = $interactor->ejecutar($slug);
        $space = $detalle['space'];

        if (! empty($space['es_restaurante']) || (is_string($space['tipo'] ?? null) && strtolower($space['tipo']) === 'restaurante') || $slug === 'restaurante') {
            return to_route('restaurante');
        }

        $rawId = $space['id'] ?? null;
        $espacioId = is_numeric($rawId) ? (int) $rawId : 0;

        return Inertia::render('espacios/EspacioReservar', [
            ...$detalle,
            'opcionesReserva' => $opciones->ejecutar($espacioId),
        ]);
    }
}
