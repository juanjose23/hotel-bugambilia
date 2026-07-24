<?php

declare(strict_types=1);

namespace App\Http\Controllers\Habitaciones;

use App\Http\Controllers\Controller;
use App\Interactors\Landing\ObtenerHabitacionDetalleLanding;
use App\Interactors\Landing\ObtenerHabitacionesLanding;
use App\Repository\Queries\Reservas\ObtenerOpcionesReservaPublicaQuery;
use App\Support\Utilidades\FormatearPaginacion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class HabitacionController extends Controller
{
    public function index(Request $request, ObtenerHabitacionesLanding $interactor): Response
    {
        $categoria = $request->string('categoria')->toString();
        $busqueda = $request->string('buscar')->toString();
        $huespedes = $request->integer('huespedes');

        $paginator = $interactor->ejecutar(
            categoria: $categoria !== '' ? $categoria : null,
            busqueda: $busqueda !== '' ? $busqueda : null,
            huespedes: $huespedes > 0 ? $huespedes : null,
        );

        return Inertia::render('habitaciones/Habitaciones', [
            'rooms' => $paginator->items(),
            'categorias' => $interactor->categorias(),
            'selectedCategory' => $categoria !== '' ? $categoria : null,
            'searchQuery' => $busqueda,
            'pagination' => FormatearPaginacion::ejecutar($paginator),
        ]);
    }

    public function show(string $slug, ObtenerHabitacionDetalleLanding $interactor): Response
    {
        return Inertia::render('habitaciones/HabitacionDetalle', $interactor->ejecutar($slug));
    }

    public function mostrarReserva(
        string $slug,
        ObtenerHabitacionDetalleLanding $interactor,
        ObtenerOpcionesReservaPublicaQuery $opcionesQuery,
    ): Response {
        $data = $interactor->ejecutar($slug);

        return Inertia::render('habitaciones/HabitacionReservar', [
            'room' => $data['room'],
            'opcionesReserva' => $opcionesQuery->obtener(),
        ]);
    }
}
