<?php

declare(strict_types=1);

namespace App\BusinessLogic\Inventario\Validacion;

use App\Enums\Inventario\EstadoLote;

final readonly class ReglasLotesRecepcion
{
    /**
     * @param  array{id: int|string}  $item
     * @param  array<int, array{disponible: float|int, cuarentena: float|int}>  $decisionesDiscrepancia
     * @return array<int, array{codigo_sufijo: string, estado: EstadoLote, cantidad: float}>
     */
    public function determinarDistribucionLotes(
        string $estadoRecepcion,
        float $cantidad,
        array $item,
        array $decisionesDiscrepancia,
    ): array {
        return match ($estadoRecepcion) {
            'ConDiscrepancia' => $this->distribucionConDiscrepancia(
                itemId: (int) $item['id'],
                decisiones: $decisionesDiscrepancia,
            ),

            'EnCuarentena' => [
                $this->crearLote(
                    codigoSufijo: '',
                    estado: EstadoLote::Cuarentena,
                    cantidad: $cantidad,
                ),
            ],

            'Completa', 'Parcial' => [
                $this->crearLote(
                    codigoSufijo: '',
                    estado: EstadoLote::Disponible,
                    cantidad: $cantidad,
                ),
            ],

            default => [
                $this->crearLote(
                    codigoSufijo: '',
                    estado: EstadoLote::Cuarentena,
                    cantidad: $cantidad,
                ),
            ],
        };
    }

    /**
     * @return array{costo_unitario: float, costo_total: float}
     */
    public function calcularCostos(
        float $precioUnitario,
        float $tasaCambio,
        float $unidadesPorEmpaque,
        float $cantidad,
    ): array {
        $costoUnitario = ($precioUnitario * $tasaCambio)
             / max($unidadesPorEmpaque, 1.0);

        return [
            'costo_unitario' => $costoUnitario,
            'costo_total' => $costoUnitario * $cantidad,
        ];
    }

    /**
     * @param  array<int, array{disponible: float|int, cuarentena: float|int}>  $decisiones
     * @return array<int, array{codigo_sufijo: string, estado: EstadoLote, cantidad: float}>
     */
    private function distribucionConDiscrepancia(
        int $itemId,
        array $decisiones,
    ): array {
        if (! isset($decisiones[$itemId])) {
            return [];
        }

        $decision = $decisiones[$itemId];

        $distribucion = [];

        if ($decision['disponible'] > 0) {
            $distribucion[] = $this->crearLote(
                codigoSufijo: '-DISP',
                estado: EstadoLote::Disponible,
                cantidad: (float) $decision['disponible'],
            );
        }

        if ($decision['cuarentena'] > 0) {
            $distribucion[] = $this->crearLote(
                codigoSufijo: '-CUAR',
                estado: EstadoLote::Cuarentena,
                cantidad: (float) $decision['cuarentena'],
            );
        }

        return $distribucion;
    }

    /**
     * @return array{codigo_sufijo: string, estado: EstadoLote, cantidad: float}
     */
    private function crearLote(
        string $codigoSufijo,
        EstadoLote $estado,
        float $cantidad,
    ): array {
        return [
            'codigo_sufijo' => $codigoSufijo,
            'estado' => $estado,
            'cantidad' => $cantidad,
        ];
    }
}
