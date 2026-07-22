<?php

declare(strict_types=1);

namespace App\BusinessLogic\Compras;

use App\Repository\Models\Compras\Cotizacion;
use App\Repository\Models\Compras\Solicitud;
use Illuminate\Support\Collection;

final class ResolverEstrategiaCompra
{
    const PENALIZACION_DIARIA_RETRASO = 0.005;

    const COSTO_TRANSACCION_PROVEEDOR = 25.00;

    const MARGEN_PREFERENCIA_UNICO = 0.03;

    /**
     * @param  Collection<int, Cotizacion>  $cotizaciones
     * @return array<string, mixed>
     */
    public function ejecutar(Solicitud $solicitud, Collection $cotizaciones): array
    {
        $totalItemsRequeridos = $solicitud->items->count();

        if ($cotizaciones->isEmpty()) {
            return [
                'tipo' => 'SIN_DATOS',
                'color' => 'gray',
                'mensaje' => 'No hay cotizaciones suficientes para realizar un análisis comparativo.',
                'ahorro' => 0,
            ];
        }

        $analisisSplit = $this->calcularTCOCompraDividida($solicitud, $cotizaciones);

        $opcionesUnicas = $this->evaluarProveedoresUnicos($cotizaciones, $totalItemsRequeridos);

        return $this->determinarMejorEstrategia($analisisSplit, $opcionesUnicas, $cotizaciones);
    }

    /**
     * @param  Collection<int, Cotizacion>  $cotizaciones
     * @return Collection<int, array{id: int, nombre: string, tco: float, precio_bruto: float, dias: float, razon: string}>
     */
    protected function evaluarProveedoresUnicos(Collection $cotizaciones, int $totalItems): Collection
    {
        /** @var Collection<int, array{id: int, nombre: string, tco: float, precio_bruto: float, dias: float, razon: string}> $filtered */
        $filtered = $cotizaciones->filter(fn ($cot) => $cot->items->count() === $totalItems)
            ->map(function ($cot) {
                $costoFinanciero = (float) $cot->total;
                $costoLogistico = $costoFinanciero * ((float) $cot->dias_entrega * self::PENALIZACION_DIARIA_RETRASO);
                $costoAdministrativo = self::COSTO_TRANSACCION_PROVEEDOR;

                return [
                    'id' => (int) $cot->id,
                    'nombre' => $cot->proveedor?->persona?->personaJuridica->razon_social ?? 'Proveedor #'.$cot->proveedor_id,
                    'tco' => $costoFinanciero + $costoLogistico + $costoAdministrativo,
                    'precio_bruto' => $costoFinanciero,
                    'dias' => (float) $cot->dias_entrega,
                    'razon' => 'Capacidad de surtido completo',
                ];
            });

        return $filtered->sortBy('tco');
    }

    /**
     * @param  Collection<int, Cotizacion>  $cotizaciones
     * @return array{tco: float, precio_bruto: float, dias: int, num_proveedores: int}
     */
    protected function calcularTCOCompraDividida(Solicitud $solicitud, Collection $cotizaciones): array
    {
        $costoFinanciero = 0;
        $proveedoresIds = [];
        $maxDias = 0;

        foreach ($solicitud->items as $sItem) {
            $mejorItem = null;
            $mejorCot = null;

            foreach ($cotizaciones as $cot) {
                $cItem = $cot->items->where('producto_id', $sItem->producto_id)->first();
                if ($cItem && ($mejorItem === null || (float) $cItem->precio_unitario < (float) $mejorItem->precio_unitario)) {
                    $mejorItem = $cItem;
                    $mejorCot = $cot;
                }
            }

            if ($mejorItem && $mejorCot) {
                $costoFinanciero += ((float) $sItem->cantidad_solicitada * (float) $mejorItem->precio_unitario);
                $proveedoresIds[$mejorCot->id] = true;
                $maxDias = max($maxDias, (int) $mejorCot->dias_entrega);
            }
        }

        $costoTotalConImpuestos = $costoFinanciero * 1.15;
        $costoLogistico = $costoTotalConImpuestos * ($maxDias * self::PENALIZACION_DIARIA_RETRASO);
        $costoAdministrativo = count($proveedoresIds) * self::COSTO_TRANSACCION_PROVEEDOR;

        return [
            'tco' => $costoTotalConImpuestos + $costoLogistico + $costoAdministrativo,
            'precio_bruto' => $costoTotalConImpuestos,
            'dias' => $maxDias,
            'num_proveedores' => count($proveedoresIds),
        ];
    }

    /**
     * @param  array{tco: float, precio_bruto: float, dias: int, num_proveedores: int}  $split
     * @param  Collection<int, array{id: int, nombre: string, tco: float, precio_bruto: float, dias: float, razon: string}>  $unicas
     * @param  Collection<int, Cotizacion>  $cotizaciones
     * @return array<string, mixed>
     */
    protected function determinarMejorEstrategia(array $split, Collection $unicas, Collection $cotizaciones): array
    {
        $mejorSolo = $unicas->first();

        $proveedorFlash = $cotizaciones->where('dias_entrega', '<=', 2)->sortBy('total')->first();
        if ($proveedorFlash && $mejorSolo && $proveedorFlash->id !== $mejorSolo['id']) {
            $flashTotal = floatval($proveedorFlash->total);
            $sobrecostoFlash = (($flashTotal / floatval($mejorSolo['precio_bruto'])) - 1) * 100;
            if ($sobrecostoFlash < 12) {
                $pJur = $proveedorFlash->proveedor?->persona?->personaJuridica;
                $nombreProv = $pJur->razon_social ?? 'Proveedor desconocido';

                return [
                    'tipo' => 'PROVEEDOR ÚNICO',
                    'subtipo' => 'URGENCIA_LOGISTICA',
                    'color' => 'success',
                    'cotizacion_id' => $proveedorFlash->id,
                    'ahorro' => 0,
                    'mensaje' => "RECOMENDACIÓN POR URGENCIA: Se recomienda comprar todo a **{$nombreProv}**. Aunque no es la opción más barata, su entrega en {$proveedorFlash->dias_entrega} días es crítica para la operación y el sobrecosto es aceptable (".round($sobrecostoFlash, 1).'%).',
                ];
            }
        }

        $splitTco = (float) $split['tco'];
        $mejorSoloTco = 0.0;
        if ($mejorSolo) {
            $mejorSoloTco = (float) $mejorSolo['tco'];
        }

        if ($mejorSolo && $mejorSoloTco <= ($splitTco * (1 + self::MARGEN_PREFERENCIA_UNICO))) {
            $mejorSoloNombre = $mejorSolo['nombre'];
            $splitNumProv = (string) $split['num_proveedores'];

            return [
                'tipo' => 'PROVEEDOR ÚNICO',
                'subtipo' => 'EFICIENCIA_OPERATIVA',
                'color' => 'success',
                'cotizacion_id' => $mejorSolo['id'],
                'ahorro' => max(0, $splitTco - $mejorSoloTco),
                'mensaje' => "RECOMENDACIÓN POR SIMPLICIDAD: Comprar todo a **{$mejorSoloNombre}**. Centralizar la compra evita la fragmentación logística y los costos administrativos de gestionar {$splitNumProv} proveedores.",
            ];
        }

        return [
            'tipo' => 'COMPRA DIVIDIDA',
            'subtipo' => 'AHORRO_ECONOMICO',
            'color' => 'warning',
            'cotizacion_id' => null,
            'ahorro' => $mejorSolo ? ($mejorSoloTco - $splitTco) : 0,
            'mensaje' => 'RECOMENDACIÓN POR PRECIO: Se recomienda una **Compra Dividida**. El volumen de ahorro financiero compensa con creces los costos adicionales de transporte y administración.',
        ];
    }
}
