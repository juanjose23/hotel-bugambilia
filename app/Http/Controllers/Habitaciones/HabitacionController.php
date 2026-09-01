<?php

declare(strict_types=1);

namespace App\Http\Controllers\Habitaciones;

use App\Http\Controllers\Controller;
use App\Interactors\Landing\ObtenerHabitacionDetalleLanding;
use App\Interactors\Landing\ObtenerHabitacionesLanding;
use App\Interactors\Landing\ObtenerHabitacionReservaLanding;
use App\Support\Utilidades\FormatearPaginacion;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
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
        Request $request,
        ObtenerHabitacionDetalleLanding $interactor,
    ): Response {
        $data = $interactor->ejecutar($slug);

        return Inertia::render('reservas/ReservarHabitacion', [
            ...$data,
            'initialCheckIn' => $request->query('check_in', ''),
            'initialCheckOut' => $request->query('check_out', ''),
            'initialHuespedes' => $request->query('huespedes', '2'),
        ]);
    }

    public function disponibilidad(
        string $slug,
        Request $request,
        ObtenerHabitacionReservaLanding $interactor,
    ): JsonResponse {
        $validated = $request->validate([
            'fecha_check_in' => ['required', 'date'],
            'fecha_check_out' => ['required', 'date', 'after:fecha_check_in'],
            'adultos' => ['nullable', 'integer', 'min:1', 'max:20'],
            'ninos' => ['nullable', 'integer', 'min:0', 'max:20'],
        ]);

        return response()->json($interactor->recomendarDisponibilidad(
            slug: $slug,
            checkIn: CarbonImmutable::parse((string) $validated['fecha_check_in']),
            checkOut: CarbonImmutable::parse((string) $validated['fecha_check_out']),
            adultos: is_numeric($validated['adultos'] ?? null) ? (int) $validated['adultos'] : 1,
            ninos: is_numeric($validated['ninos'] ?? null) ? (int) $validated['ninos'] : 0,
        ));
    }

    public function diasAgotados(
        string $slug,
        Request $request,
        ObtenerHabitacionReservaLanding $interactor,
    ): JsonResponse {
        $meses = is_numeric($request->query('meses'))
            ? min(18, max(1, (int) $request->query('meses')))
            : 12;

        $adultos = is_numeric($request->query('adultos'))
            ? max(1, (int) $request->query('adultos'))
            : null;

        $ninos = is_numeric($request->query('ninos'))
            ? max(0, (int) $request->query('ninos'))
            : null;

        return response()->json($interactor->calendarioDisponibilidad($slug, $meses, $adultos, $ninos));
    }
}
