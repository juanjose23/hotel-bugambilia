<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reservas;

use App\BusinessLogic\Reservas\CalcularResumenRestaurante;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerDatosPedidoFormQuery;

final readonly class CalcularResumenRestauranteQuery
{
    public function __construct(
        private ObtenerTarifasReservaQuery $tarifas,
        private ObtenerDatosPedidoFormQuery $pedidos,
        private CalcularResumenRestaurante $calcular,
    ) {}

    /**
     * @param  array<int, mixed>  $espaciosAdicionales
     * @param  array<int, mixed>  $itemsPreorden
     * @return array{
     *     horas: int,
     *     mesas_requeridas: int,
     *     mesas_seleccionadas: int,
     *     capacidad_total: int,
     *     costo_mesas: float,
     *     costo_preorden: float,
     *     subtotal: float,
     *     abono_50: float,
     *     mesas_seleccionadas_nombres: array<int, string>,
     *     mesas_sugeridas: array<int, array{
     *         id: int,
     *         nombre: string,
     *         capacidad: int
     *     }>
     * }
     */
    public function ejecutar(
        ?int $mesaPrincipalId,
        int $comensales,
        int $horas,
        array $espaciosAdicionales,
        array $itemsPreorden,
        bool $cobrarTarifaMesa = true,
    ): array {
        $ids = $mesaPrincipalId !== null
            ? [$mesaPrincipalId]
            : [];

        foreach ($espaciosAdicionales as $espacio) {
            if (
                ! is_array($espacio)
                || ! is_numeric($espacio['espacio_id'] ?? null)
            ) {
                continue;
            }

            $ids[] = (int) $espacio['espacio_id'];
        }

        /** @var array<int, int> $ids */
        $ids = array_values(array_unique($ids));

        $espacios = Espacio::query()
            ->whereKey($ids)
            ->get()
            ->keyBy('id');

        /**
         * @var array<int, array{
         *     capacidad: int,
         *     tarifa: float,
         *     por_hora: bool
         * }> $mesas
         */
        $mesas = [];

        foreach ($ids as $id) {
            $espacio = $espacios->get($id);

            if (! $espacio instanceof Espacio) {
                continue;
            }

            $mesas[] = [
                'capacidad' => max(
                    1,
                    (int) $espacio->capacidad_personas,
                ),
                'tarifa' => $this->tarifas->espacio($id),
                'por_hora' => $this->tarifas->espacioEsPorHora($id),
            ];
        }

        /**
         * @var array<int, array{
         *     cantidad: int,
         *     precio: float
         * }> $preorden
         */
        $preorden = [];

        foreach ($itemsPreorden as $item) {
            if (
                ! is_array($item)
                || ! is_numeric($item['plato_id'] ?? null)
            ) {
                continue;
            }

            $platoId = (int) $item['plato_id'];

            $precio = $this->pedidos->precioActualDePlato($platoId);

            $preorden[] = [
                'cantidad' => max(
                    1,
                    $this->entero(
                        $item['cantidad'] ?? null,
                        1,
                    ),
                ),
                'precio' => $precio !== null
                    ? $precio
                    : 0.0,
            ];
        }

        $resumen = $this->calcular->calcular(
            $comensales,
            $horas,
            $mesas,
            $preorden,
            $cobrarTarifaMesa,
        );

        $seleccionadasNombres = $espacios
            ->values()
            ->map(
                static fn (Espacio $espacio): string => (string) $espacio->nombre,
            )
            ->values()
            ->all();

        $capacidadFaltante = max(
            0,
            $comensales - $resumen['capacidad_total'],
        );

        /**
         * @var array<int, array{
         *     id: int,
         *     nombre: string,
         *     capacidad: int
         * }> $sugeridas
         */
        $sugeridas = [];

        if (
            $capacidadFaltante > 0
            && $mesaPrincipalId !== null
        ) {
            $principal = $espacios->get($mesaPrincipalId);

            if ($principal instanceof Espacio) {
                $candidatas = Espacio::query()
                    ->where(
                        'padre_id',
                        $principal->padre_id,
                    )
                    ->where(
                        'tipo',
                        TipoEspacio::MESA,
                    )
                    ->where(
                        'reservable',
                        true,
                    )
                    ->where(
                        'estado',
                        '!=',
                        0,
                    )
                    ->whereNotIn(
                        'id',
                        $ids,
                    )
                    ->orderBy(
                        'capacidad_personas',
                    )
                    ->orderBy(
                        'nombre',
                    )
                    ->get();

                foreach ($candidatas as $candidata) {
                    $capacidad = max(
                        1,
                        (int) $candidata->capacidad_personas,
                    );

                    $sugeridas[] = [
                        'id' => (int) $candidata->id,
                        'nombre' => (string) $candidata->nombre,
                        'capacidad' => $capacidad,
                    ];

                    $capacidadFaltante -= $capacidad;
                }
            }
        }

        return [
            ...$resumen,
            'total' => $resumen['subtotal'],
            'mesas_seleccionadas_nombres' => $seleccionadasNombres,
            'mesas_sugeridas' => $sugeridas,
        ];
    }

    private function entero(
        mixed $valor,
        int $default = 0,
    ): int {
        if (is_int($valor)) {
            return $valor;
        }

        if (is_numeric($valor)) {
            return (int) $valor;
        }

        return $default;
    }
}
