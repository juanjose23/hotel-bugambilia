<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Interactors\Landing\ObtenerEspacioDetalleLanding;
use App\Interactors\Landing\ObtenerEspaciosLanding;
use App\Interactors\Landing\ObtenerHabitacionDetalleLanding;
use App\Interactors\Landing\ObtenerHabitacionesLanding;
use App\Interactors\Landing\ObtenerReservasClienteLanding;
use App\Interactors\Landing\ObtenerRestauranteLanding;
use App\Interactors\Landing\ObtenerServicioDetalleLanding;
use App\Interactors\Landing\ObtenerServiciosLanding;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;

final class PublicPageController extends Controller
{
    public function servicios(Request $request, ObtenerServiciosLanding $interactor): Response
    {
        $categoria = $request->query('categoria');
        $busqueda = $request->query('buscar');

        $categoriasConConteo = $interactor->categoriasConConteo();
        $categoriaMasPopular = $categoriasConConteo[0]['nombre'] ?? null;

        // Si no se proporcionó param de categoría, seleccionar la que tiene más servicios por defecto
        $categoriaSeleccionada = is_string($categoria) ? $categoria : $categoriaMasPopular;

        $paginator = $interactor->ejecutar(
            categoria: $categoriaSeleccionada,
            busqueda: is_string($busqueda) ? $busqueda : null
        );

        return Inertia::render('servicios/Servicios', [
            'services' => $paginator->items(),
            'categorias' => array_column($categoriasConConteo, 'nombre'),
            'categoriaMasPopular' => $categoriaMasPopular,
            'selectedCategory' => $categoriaSeleccionada,
            'searchQuery' => is_string($busqueda) ? $busqueda : '',
            'pagination' => $this->formatearPaginacion($paginator),
        ]);
    }

    public function servicioDetalle(string $slug, ObtenerServicioDetalleLanding $interactor): Response
    {
        return Inertia::render('servicios/ServicioDetalle', $interactor->ejecutar($slug));
    }

    public function habitaciones(Request $request, ObtenerHabitacionesLanding $interactor): Response
    {
        $categoria = $request->query('categoria');
        $busqueda = $request->query('buscar');

        $paginator = $interactor->ejecutar(
            categoria: is_string($categoria) ? $categoria : null,
            busqueda: is_string($busqueda) ? $busqueda : null
        );

        return Inertia::render('habitaciones/Habitaciones', [
            'rooms' => $paginator->items(),
            'categorias' => $interactor->categorias(),
            'selectedCategory' => $categoria,
            'searchQuery' => is_string($busqueda) ? $busqueda : '',
            'pagination' => $this->formatearPaginacion($paginator),
        ]);
    }

    public function habitacionDetalle(string $slug, ObtenerHabitacionDetalleLanding $interactor): Response
    {
        return Inertia::render('habitaciones/HabitacionDetalle', $interactor->ejecutar($slug));
    }

    public function acercaDe(): Response
    {
        return Inertia::render('acerca-de/AcercaDe');
    }

    public function contacto(): Response
    {
        return Inertia::render('contacto/Contacto');
    }

    public function login(): Response
    {
        return Inertia::render('auth/Login');
    }

    public function registro(): Response
    {
        return Inertia::render('auth/Registro');
    }

    public function pago(): Response
    {
        return Inertia::render('pago/Pago');
    }

    public function misReservas(Request $request, ObtenerReservasClienteLanding $interactor): Response
    {
        $codigo = $request->query('codigo');
        $reservas = $interactor->ejecutar(is_string($codigo) ? $codigo : null);

        return Inertia::render('reservas/MisReservas', [
            'reservas' => $reservas,
            'codigoBusqueda' => is_string($codigo) ? $codigo : '',
        ]);
    }

    public function favoritos(): Response
    {
        return Inertia::render('favoritos/Favoritos');
    }

    public function restaurante(ObtenerRestauranteLanding $interactor): Response
    {
        return Inertia::render('restaurante/Restaurante', $interactor->ejecutar());
    }

    public function espacios(Request $request, ObtenerEspaciosLanding $interactor): Response
    {
        $tipo = $request->query('tipo');
        $espacios = $interactor->ejecutar(is_string($tipo) ? $tipo : null);
        $tipos = $interactor->tiposDisponibles();

        return Inertia::render('espacios/Espacios', [
            'espacios' => $espacios,
            'tipos' => $tipos,
            'tipoSeleccionado' => is_string($tipo) ? $tipo : 'TODOS',
        ]);
    }

    public function espacioDetalle(int $id, ObtenerEspacioDetalleLanding $interactor): Response
    {
        return Inertia::render('espacios/EspacioDetalle', $interactor->ejecutar($id));
    }

    /**
     * @template T
     *
     * @param  LengthAwarePaginator<int, T>  $paginator
     * @return array{
     *     current_page:int,
     *     last_page:int,
     *     per_page:int,
     *     total:int
     * }
     */
    private function formatearPaginacion(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'next_page_url' => $paginator->nextPageUrl(),
            'prev_page_url' => $paginator->previousPageUrl(),
            'first_page_url' => $paginator->url(1),
            'last_page_url' => $paginator->url($paginator->lastPage()),
            'links' => $paginator->linkCollection()->toArray(),
        ];
    }
}
