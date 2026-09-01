<?php

declare(strict_types=1);

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Interactors\Landing\ObtenerPromocionesLanding;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class PromocionPublicoController extends Controller
{
    public function index(Request $request, ObtenerPromocionesLanding $interactor): Response
    {
        $categoria = $request->string('categoria')->toString();
        $busqueda = $request->string('buscar')->toString();

        $data = $interactor->ejecutar(
            categoria: $categoria !== '' ? $categoria : null,
            busqueda: $busqueda !== '' ? $busqueda : null,
        );

        return Inertia::render('promociones/Promociones', [
            'promociones' => $data['promociones'],
            'categorias' => $data['categorias'],
            'selectedCategory' => $categoria !== '' ? $categoria : null,
            'searchQuery' => $busqueda,
        ]);
    }
}
