<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use Filament\Pages\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

final class RedirigirFacturacionController
{
    public function __invoke(Request $request): RedirectResponse
    {
        $route = $request->route();
        abort_unless($route instanceof Route, 404);
        $destino = $route->defaults['destino_facturacion'] ?? null;
        abort_unless(is_string($destino) && is_a($destino, Page::class, true), 404);
        $parametros = array_replace($request->query->all(), $route->parameters());
        unset($parametros['destino_facturacion']);

        return redirect()->to($destino::getUrl($parametros));
    }
}
