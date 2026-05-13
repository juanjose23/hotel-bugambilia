<?php

namespace App\UseCases\Compras;

use App\Models\Compras\Cotizacion;
use App\Models\Compras\Solicitud;
use Illuminate\Support\Collection;

/**
 * Caso de Uso: Análisis de Abastecimiento Estratégico (TCO)
 *
 * Este caso de uso aplica inteligencia logística para recomendar la mejor estrategia de compra
 * considerando costo financiero, tiempos de entrega y costos administrativos por fragmentación.
 */
class ObtenerRecomendacionLogistica
{
    // Constantes de mercado y logística interna
    const PENALIZACION_DIARIA_RETRASO = 0.005; // 0.5% del valor total por día de espera

    const COSTO_TRANSACCION_PROVEEDOR = 25.00; // Costo de procesar cada proveedor adicional (administrativo/recepción)

    const MARGEN_PREFERENCIA_UNICO = 0.03;      // 3% de margen para preferir un solo proveedor por simplicidad

    /** @return array<string, mixed> */
    public function execute(Solicitud $solicitud): array
    {
        $cotizaciones = $solicitud->cotizaciones()->with(['proveedor.persona.personaJuridica', 'items'])->get();
        $totalItemsRequeridos = $solicitud->items->count();

        if ($cotizaciones->isEmpty()) {
            return [
                'tipo' => 'SIN_DATOS',
                'color' => 'gray',
                'mensaje' => 'No hay cotizaciones suficientes para realizar un análisis comparativo.',
                'ahorro' => 0,
            ];
        }

        // 1. Análisis de Compra Dividida (Mejor precio por ítem)
        $analisisSplit = $this->calcularTCOCompraDividida($solicitud, $cotizaciones);

        // 2. Análisis de Proveedores Únicos (Capacidad total)
        $opcionesUnicas = $this->evaluarProveedoresUnicos($cotizaciones, $totalItemsRequeridos);

        // 3. Determinar la mejor opción basada en TCO
        return $this->determinarMejorEstrategia($analisisSplit, $opcionesUnicas, $cotizaciones);
    }

    /** @param Collection<int, Cotizacion> $cotizaciones
     * @return Collection<int, array{id: mixed, nombre: mixed, tco: float, precio_bruto: float, dias: mixed, razon: string}> */
    protected function evaluarProveedoresUnicos(Collection $cotizaciones, int $totalItems): Collection
    {
        /** @var Collection<int, array{id: mixed, nombre: mixed, tco: float, precio_bruto: float, dias: mixed, razon: string}> $filtered */
        $filtered = $cotizaciones->filter(fn ($cot) => $cot->items->count() === $totalItems)
            ->map(function ($cot) {
                $costoFinanciero = (float) $cot->total;
                $costoLogistico = $costoFinanciero * ($cot->dias_entrega * self::PENALIZACION_DIARIA_RETRASO);
                $costoAdministrativo = self::COSTO_TRANSACCION_PROVEEDOR;

                return [
                    'id' => $cot->id,
                    'nombre' => $cot->proveedor?->persona->personaJuridica->razon_social ?? 'Proveedor #'.$cot->proveedor_id,
                    'tco' => $costoFinanciero + $costoLogistico + $costoAdministrativo,
                    'precio_bruto' => $costoFinanciero,
                    'dias' => $cot->dias_entrega,
                    'razon' => 'Capacidad de surtido completo',
                ];
            });

        /** @var Collection<int, array{id: mixed, nombre: mixed, tco: float, precio_bruto: float, dias: mixed, razon: string}> $sorted */
        $sorted = $filtered->sortBy('tco');

        return $sorted;
    }

    /** @param Collection<int, Cotizacion> $cotizaciones
     * @return array{tco: float, precio_bruto: float, dias: int, num_proveedores: int} */
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
                if ($cItem && ($mejorItem === null || $cItem->precio_unitario < $mejorItem->precio_unitario)) {
                    $mejorItem = $cItem;
                    $mejorCot = $cot;
                }
            }

            if ($mejorItem) {
                $costoFinanciero += ($sItem->cantidad_solicitada * $mejorItem->precio_unitario);
                $proveedoresIds[$mejorCot->id] = true;
                $maxDias = max($maxDias, $mejorCot->dias_entrega);
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

    /** @param array<string, mixed> $split
     * @param Collection<int, array{id: mixed, nombre: mixed, tco: float, precio_bruto: float, dias: mixed, razon: string}> $unicas
     * @param Collection<int, Cotizacion> $cotizaciones
     * @return array<string, mixed> */
    protected function determinarMejorEstrategia(array $split, Collection $unicas, Collection $cotizaciones): array
    {
        $mejorSolo = $unicas->first();

        // Escenario A: Proveedor Flash (Urgencia Crítica)
        $proveedorFlash = $cotizaciones->where('dias_entrega', '<=', 2)->sortBy('total')->first();
        if ($proveedorFlash && $mejorSolo && $proveedorFlash->id !== $mejorSolo['id']) {
            $sobrecostoFlash = (($proveedorFlash->total / $mejorSolo['precio_bruto']) - 1) * 100;
            if ($sobrecostoFlash < 12) { // Si el sobrecosto por velocidad es menor al 12%
                return [
                    'tipo' => 'PROVEEDOR ÚNICO',
                    'subtipo' => 'URGENCIA_LOGISTICA',
                    'color' => 'success',
                    'cotizacion_id' => $proveedorFlash->id,
                    'ahorro' => 0,
                    'mensaje' => "RECOMENDACIÓN POR URGENCIA: Se recomienda comprar todo a **{$proveedorFlash->proveedor->persona->personaJuridica->razon_social}**. Aunque no es la opción más barata, su entrega en {$proveedorFlash->dias_entrega} días es crítica para la operación y el sobrecosto es aceptable (".round($sobrecostoFlash, 1).'%).',
                ];
            }
        }

        // Escenario B: Proveedor Único Eficiente
        if ($mejorSolo && $mejorSolo['tco'] <= ($split['tco'] * (1 + self::MARGEN_PREFERENCIA_UNICO))) {
            return [
                'tipo' => 'PROVEEDOR ÚNICO',
                'subtipo' => 'EFICIENCIA_OPERATIVA',
                'color' => 'success',
                'cotizacion_id' => $mejorSolo['id'],
                'ahorro' => max(0, $split['tco'] - $mejorSolo['tco']),
                'mensaje' => "RECOMENDACIÓN POR SIMPLICIDAD: Comprar todo a **{$mejorSolo['nombre']}**. Centralizar la compra evita la fragmentación logística y los costos administrativos de gestionar {$split['num_proveedores']} proveedores.",
            ];
        }

        // Escenario C: Compra Dividida (Máximo ahorro financiero)
        return [
            'tipo' => 'COMPRA DIVIDIDA',
            'subtipo' => 'AHORRO_ECONOMICO',
            'color' => 'warning',
            'cotizacion_id' => null,
            'ahorro' => $mejorSolo ? ($mejorSolo['tco'] - $split['tco']) : 0,
            'mensaje' => 'RECOMENDACIÓN POR PRECIO: Se recomienda una **Compra Dividida**. El volumen de ahorro financiero compensa con creces los costos adicionales de transporte y administración.',
        ];
    }
}
