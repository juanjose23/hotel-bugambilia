<?php

declare(strict_types=1);

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Repository\Models\Servicios\Servicio;
use Inertia\Inertia;
use Inertia\Response;

final class PagoController extends Controller
{
    public function __invoke(): Response
    {
        $serviciosExtras = Servicio::query()
            ->activos()
            ->where('web', true)
            ->with(['precios.moneda'])
            ->get()
            ->map(function (Servicio $s): array {
                $precioObj = $s->precios->first();

                return [
                    'id' => (string) $s->id,
                    'nombre' => (string) $s->nombre,
                    'descripcion' => (string) ($s->descripcion ?? ''),
                    'precio' => $precioObj ? (float) $precioObj->precio : 0.0,
                    'moneda' => $precioObj?->moneda->simbolo ?? '$',
                ];
            })
            ->values()
            ->all();

        return Inertia::render('pago/Pago', [
            'serviciosExtras' => $serviciosExtras,
        ]);
    }
}
