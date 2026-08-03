<?php

namespace App\BusinessLogic\Reservas;

use App\Repository\Queries\Reservas\CalcularResumenRestauranteQuery;
use DomainException;

final readonly class CalcularResumenRestauranteLogica
{
    public function __construct(
        private CalcularResumenRestauranteQuery $calcularResumenRestaurante,
    ) {}

    /**
     * @param  array<string, mixed>  $datos
     * @param  array<int, mixed>  $espaciosAdicionales
     * @param  array<int, mixed>  $itemsPreorden
     * @return array<string, mixed>
     */
    public function ejecutar(int $entidadPrincipalId, array $datos, array $espaciosAdicionales, array $itemsPreorden): array
    {
        $adultos = is_numeric($datos['adultos'] ?? null) ? (int) $datos['adultos'] : 1;
        $duracionHoras = is_numeric($datos['duracion_horas'] ?? null) ? (int) $datos['duracion_horas'] : 1;

        $cobrarTarifaMesa = (bool) ($datos['cobrar_tarifa_mesa'] ?? false);

        $resumen = $this->calcularResumenRestaurante->ejecutar(
            mesaPrincipalId: $entidadPrincipalId,
            comensales: $adultos,
            horas: $duracionHoras,
            espaciosAdicionales: $espaciosAdicionales,
            itemsPreorden: $itemsPreorden,
            cobrarTarifaMesa: $cobrarTarifaMesa,
        );

        if ($resumen['capacidad_total'] < $adultos) {
            $faltantes = max(0, $resumen['mesas_requeridas'] - $resumen['mesas_seleccionadas']);
            $mesasSugeridas = implode(' + ', array_column($resumen['mesas_sugeridas'], 'nombre'));
            $sugerencia = $mesasSugeridas !== ''
                ? " Mesas sugeridas para unir: $mesasSugeridas."
                : ' No existen mesas adicionales suficientes para completar la capacidad.';

            throw new DomainException("La capacidad seleccionada es insuficiente. Debe agregar $faltantes mesa(s) adicional(es) para atender a todos los comensales.$sugerencia");
        }

        return $resumen;
    }
}
