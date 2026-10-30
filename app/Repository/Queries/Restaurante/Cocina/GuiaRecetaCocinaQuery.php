<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Cocina;

use App\Enums\Restaurante\UbicacionCocina;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Persistencia\Restaurante\ExistenciasCocinaRepositorio;

final readonly class GuiaRecetaCocinaQuery
{
    public function __construct(private ExistenciasCocinaRepositorio $existencias) {}

    /** @return array{nombre: string, bodega: string, pasos: list<string>, ingredientes: list<array{nombre: string, unidad: string, necesaria: float, disponible: float, faltante: float}>} */
    public function ejecutar(?int $platoId, int $porciones, ?int $ubicacionId = null): array
    {
        $plato = $platoId !== null ? Plato::query()->with(['receta', 'ingredientes.variante.producto.unidadMedida', 'ingredientes.variante.unidadMedida'])->find($platoId) : null;
        $bodega = $ubicacionId !== null ? Ubicacion::query()->find($ubicacionId) : Ubicacion::query()->where('nombre', UbicacionCocina::RESTAURANTE->value)->first();
        $guia = ['nombre' => $plato->nombre ?? 'Selecciona un platillo', 'bodega' => $bodega->nombre ?? 'Bodega no configurada', 'pasos' => [], 'ingredientes' => []];
        if ($plato === null) {
            return $guia;
        }
        $instrucciones = $plato->getAttribute('instrucciones_preparacion');
        if (is_string($instrucciones)) {
            $guia['pasos'] = array_values(array_filter(array_map('trim', explode("\n", $instrucciones)), fn (string $paso): bool => $paso !== ''));
        }
        $rendimiento = max((float) ($plato->receta->rendimiento_porciones ?? 1), .0001);
        // Aggregate repeated variants before comparing against stock.
        $agrupados = [];
        foreach ($plato->ingredientes as $ingrediente) {
            $variante = $ingrediente->variante;
            if ($variante === null) {
                continue;
            }
            $cantidad = (float) $ingrediente->cantidad * max(1, $porciones) / $rendimiento;
            if (isset($agrupados[$variante->id])) {
                $agrupados[$variante->id]['necesaria'] += $cantidad;

                continue;
            }
            $disponible = $bodega !== null ? $this->existencias->cantidad($this->existencias->porVariante($variante->id, $bodega->id)) : 0.0;
            $agrupados[$variante->id] = ['nombre' => ($variante->producto->nombre ?? 'Ingrediente').' · '.$variante->nombre_variante, 'unidad' => $variante->unidadMedida->nombre ?? $variante->producto->unidadMedida->nombre ?? 'unidades', 'necesaria' => $cantidad, 'disponible' => $disponible, 'faltante' => 0.0];
        }
        foreach ($agrupados as $ingrediente) {
            $ingrediente['necesaria'] = round($ingrediente['necesaria'], 4);
            $ingrediente['faltante'] = max(0.0, round($ingrediente['necesaria'] - $ingrediente['disponible'], 4));
            $guia['ingredientes'][] = $ingrediente;
        }

        return $guia;
    }
}
