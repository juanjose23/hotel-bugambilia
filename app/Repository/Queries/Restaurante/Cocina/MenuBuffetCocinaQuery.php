<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Cocina;

use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Restaurante\Buffet;
use App\Repository\Models\Restaurante\Plato;

final class MenuBuffetCocinaQuery
{
    /** @return array<int, string> */
    public function platos(): array
    {
        $opciones = [];
        foreach (Plato::query()->where('estado', 1)->whereNotNull('producto_receta_id')->orderBy('nombre')->get() as $plato) {
            $opciones[$plato->id] = $plato->nombre;
        }

        return $opciones;
    }

    /** @return array<int, string> */
    public function variantes(): array
    {
        $opciones = [];
        foreach (ProductoVariante::query()->where('estado', 1)->with(['producto.unidadMedida', 'unidadMedida'])->orderBy('nombre_variante')->get() as $variante) {
            $opciones[$variante->id] = ($variante->producto->nombre ?? '').' · '.$variante->nombre_variante.' / '.($variante->unidadMedida->nombre ?? $variante->producto->unidadMedida->nombre ?? 'unidades');
        }

        return $opciones;
    }

    /** @return array{servicio: string, platos: list<array{nombre: string, presentacion: string, porciones: int}>} */
    public function detalle(Buffet $buffet): array
    {
        $filas = [];
        foreach ($buffet->menu()->with(['plato', 'variante.producto.unidadMedida', 'variante.unidadMedida'])->get() as $fila) {
            $filas[] = ['nombre' => $fila->plato->nombre ?? 'Platillo', 'presentacion' => ($fila->variante->producto->nombre ?? '').' · '.($fila->variante->nombre_variante ?? '').' / '.($fila->variante->unidadMedida->nombre ?? $fila->variante->producto->unidadMedida->nombre ?? 'unidades'), 'porciones' => (int) $fila->porciones_previstas];
        }

        return ['servicio' => is_string($buffet->getAttribute('tipo_servicio')) ? $buffet->getAttribute('tipo_servicio') : 'sin_definir', 'platos' => $filas];
    }
}
