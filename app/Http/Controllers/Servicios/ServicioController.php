<?php

declare(strict_types=1);

namespace App\Http\Controllers\Servicios;

use App\Http\Controllers\Controller;
use App\Interactors\Servicios\ObtenerServicioDetalle;
use App\Interactors\Servicios\ObtenerServicios;
use App\Support\Utilidades\FormatearPaginacion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ServicioController extends Controller
{
    public function index(Request $request, ObtenerServicios $interactor): Response
    {
        $categoria = $request->query('categoria');
        $busqueda = $request->query('buscar');

        $categoriasConConteo = $interactor->categoriasConConteo();
        $categoriaMasPopular = $categoriasConConteo[0]['nombre'] ?? null;

        $categoriaSeleccionada = is_string($categoria) && trim($categoria) !== '' ? trim($categoria) : null;

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
            'pagination' => FormatearPaginacion::ejecutar($paginator),
        ]);
    }

    public function show(string $slug, ObtenerServicioDetalle $interactor): Response
    {
        return Inertia::render('servicios/ServicioDetalle', $interactor->ejecutar($slug));
    }
}
