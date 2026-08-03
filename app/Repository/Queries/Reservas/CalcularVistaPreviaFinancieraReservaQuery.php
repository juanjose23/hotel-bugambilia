<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reservas;

use App\BusinessLogic\Reservas\AplicarPromocionReserva;
use App\BusinessLogic\Reservas\ValidarSeleccionAdicionales;
use App\Enums\Cuentas\BaseCalculo;
use App\Enums\Cuentas\ModoCargo;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Promociones\Promocion;
use App\Repository\Queries\Cuentas\ObtenerCargosFacturacionReservaQuery;
use Carbon\Carbon;
use Throwable;

final readonly class CalcularVistaPreviaFinancieraReservaQuery
{
    public function __construct(
        private ObtenerTarifasReservaQuery $tarifas,
        private CalcularResumenRestauranteQuery $restaurante,
        private ValidarSeleccionAdicionales $adicionales,
        private ObtenerPromocionReservaQuery $promociones,
        private AplicarPromocionReserva $aplicarPromocion,
        private ObtenerCargosFacturacionReservaQuery $cargos,
    ) {}

    /**
     * @param  array<string, mixed>  $datos
     * @return array{
     *     duracion: string,
     *     tarifa_base: float,
     *     subtotal: float,
     *     descuento: float,
     *     cargos: array<int, array{
     *         nombre: string,
     *         monto: float,
     *         obligatorio: bool
     *     }>,
     *     total_cargos: float,
     *     total: float,
     *     abono_50: float
     * }
     */
    public function ejecutar(array $datos): array
    {
        $tipo = $this->tipoReserva($datos);

        $unidades = $this->unidades($tipo, $datos);
        $tarifaBase = $this->tarifaBase($tipo, $datos);

        $servicios = $this->resolverServicios($datos);
        $espacios = $this->resolverEspacios($datos, $tipo);

        $subtotal = $this->calcularSubtotal(
            $tipo,
            $datos,
            $unidades,
            $tarifaBase,
            $servicios,
            $espacios,
        );

        $promocion = $this->obtenerPromocion($datos);

        $totales = $this->aplicarPromocion->calcular(
            $subtotal,
            $promocion?->descuento_porcentaje !== null
                ? (float) $promocion->descuento_porcentaje
                : null,
            $promocion?->descuento_monto !== null
                ? (float) $promocion->descuento_monto
                : null,
        );

        $total = (float) $totales['total'];

        $seleccionados = $this->idsSeleccionados($datos);

        $detalleCargos = $this->calcularCargos(
            subtotal: $subtotal,
            total: $total,
            totales: $totales,
            seleccionados: $seleccionados,
        );

        $totalCargos = round(
            array_sum(
                array_column($detalleCargos, 'monto')
            ),
            2
        );

        $totalFinal = round(
            $total + $totalCargos,
            2
        );

        return [
            'duracion' => $this->descripcionDuracion(
                $tipo,
                $unidades,
            ),
            'tarifa_base' => round($tarifaBase, 2),
            'subtotal' => $subtotal,
            'descuento' => (float) $totales['descuento'],
            'cargos' => $detalleCargos,
            'total_cargos' => $totalCargos,
            'total' => $totalFinal,
            'abono_50' => round($totalFinal * 0.5, 2),
        ];
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    private function tipoReserva(array $datos): ?TipoReserva
    {
        $tipo = $datos['tipo_reserva'] ?? null;

        if (! is_string($tipo)) {
            return null;
        }

        return TipoReserva::tryFrom($tipo);
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    private function unidades(
        ?TipoReserva $tipo,
        array $datos,
    ): int {
        if ($tipo === TipoReserva::RESTAURANTE) {
            return max(
                1,
                $this->entero(
                    $datos,
                    'duracion_horas',
                    1,
                ),
            );
        }

        if ($tipo !== TipoReserva::HABITACION) {
            return 1;
        }

        try {
            $fechaCheckIn = $this->fecha(
                $datos['fecha_check_in'] ?? null,
                Carbon::now(),
            );

            $fechaCheckOut = $this->fecha(
                $datos['fecha_check_out'] ?? null,
                Carbon::now()->addDay(),
            );

            return max(1, (int) $fechaCheckIn->diffInDays($fechaCheckOut));
        } catch (Throwable) {
            return 1;
        }
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    private function tarifaBase(
        ?TipoReserva $tipo,
        array $datos,
    ): float {
        return match ($tipo) {
            TipoReserva::HABITACION => $this->tarifaPorId(
                $datos,
                'habitacion_id',
                fn (int $id): float => $this->tarifas->habitacion($id),
            ),

            TipoReserva::RESTAURANTE => $this->tarifaPorId(
                $datos,
                'espacio_id',
                fn (int $id): float => $this->tarifas->espacio($id),
            ),

            TipoReserva::SERVICIO => $this->tarifaPorId(
                $datos,
                'servicio_id',
                fn (int $id): float => $this->tarifas->servicio($id),
            ),

            default => 0.0,
        };
    }

    /**
     * @param  array<string, mixed>  $datos
     * @param  callable(int): float  $resolver
     */
    private function tarifaPorId(
        array $datos,
        string $campo,
        callable $resolver,
    ): float {
        $id = $this->id($datos, $campo);

        if ($id === null) {
            return 0.0;
        }

        return $resolver($id);
    }

    /**
     * @param  array<string, mixed>  $datos
     * @param array<int, array{
     *     precio: float,
     *     cantidad: int
     * }> $servicios
     * @param array<int, array{
     *     precio: float,
     *     cantidad: int
     * }> $espacios
     */
    private function calcularSubtotal(
        ?TipoReserva $tipo,
        array $datos,
        int $unidades,
        float $tarifaBase,
        array $servicios,
        array $espacios,
    ): float {
        if ($tipo === TipoReserva::RESTAURANTE) {
            $resumen = $this->restaurante->ejecutar(
                mesaPrincipalId: $this->id(
                    $datos,
                    'espacio_id',
                ),
                comensales: max(
                    1,
                    $this->entero(
                        $datos,
                        'adultos',
                        1,
                    ),
                ),
                horas: $unidades,
                espaciosAdicionales: $this->arrayDato(
                    $datos,
                    'espacios_adicionales',
                ),
                itemsPreorden: $this->arrayDato(
                    $datos,
                    'items_preorden',
                ),
            );

            return round(
                (float) $resumen['subtotal'],
                2,
            );
        }

        $subtotal = $tarifaBase * $unidades;

        foreach ($espacios as $espacio) {
            $subtotal +=
                $espacio['precio']
                * $espacio['cantidad'];
        }

        foreach ($servicios as $servicio) {
            $subtotal +=
                $servicio['precio']
                * $servicio['cantidad'];
        }

        return round($subtotal, 2);
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    private function obtenerPromocion(array $datos): ?Promocion
    {
        $promocionId = $this->id(
            $datos,
            'promocion_id',
        );

        if ($promocionId === null) {
            return null;
        }

        try {
            return $this->promociones->vigente(
                $promocionId,
            );
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<int>
     */
    private function idsSeleccionados(array $datos): array
    {
        $valores = $datos['cargos_facturacion_ids'] ?? null;

        if (! is_array($valores)) {
            return [];
        }

        $ids = [];

        foreach ($valores as $valor) {
            if (! is_numeric($valor)) {
                continue;
            }

            $ids[] = (int) $valor;
        }

        return $ids;
    }

    /**
     * @param array{
     *     total: float|int,
     *     descuento: float|int
     * } $totales
     * @param  array<int>  $seleccionados
     * @return array<int, array{
     *     nombre: string,
     *     monto: float,
     *     obligatorio: bool
     * }>
     */
    private function calcularCargos(
        float $subtotal,
        float $total,
        array $totales,
        array $seleccionados,
    ): array {
        $detalleCargos = [];

        foreach ($this->cargos->ejecutar() as $cargo) {
            if (
                ! $cargo->obligatorio
                && ! in_array(
                    $cargo->id,
                    $seleccionados,
                    true,
                )
            ) {
                continue;
            }

            $base = $this->baseCargo(
                baseCalculo: $cargo->base_calculo,
                subtotal: $subtotal,
                total: $total,
                totales: $totales,
            );

            $monto = match ($cargo->modo_calculo) {
                ModoCargo::Porcentaje => round(
                    $base * ($cargo->valor / 100),
                    2,
                ),

                ModoCargo::MontoFijo => round(
                    $cargo->valor,
                    2,
                ),

                ModoCargo::Manual => 0.0,
            };

            $detalleCargos[] = [
                'nombre' => $cargo->nombre,
                'monto' => $monto,
                'obligatorio' => $cargo->obligatorio,
            ];

            $total += $monto;
        }

        return $detalleCargos;
    }

    /**
     * @param array{
     *     total: float|int,
     *     descuento: float|int
     * } $totales
     */
    private function baseCargo(
        BaseCalculo $baseCalculo,
        float $subtotal,
        float $total,
        array $totales,
    ): float {
        return match ($baseCalculo) {
            BaseCalculo::SubtotalBruto => $subtotal,

            BaseCalculo::SubtotalNeto => (float) $totales['total'],

            BaseCalculo::TotalConImpuestos => $total,

            BaseCalculo::BaseManual => 0.0,
        };
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<int, array{
     *     precio: float,
     *     cantidad: int
     * }>
     */
    private function resolverServicios(
        array $datos,
    ): array {
        try {
            $servicios = $this->adicionales->resolverServicios(
                $this->arrayDato(
                    $datos,
                    'servicios_adicionales',
                ),
            );

            return $this->normalizarConceptos(
                $servicios,
            );
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<int, array{
     *     precio: float,
     *     cantidad: int
     * }>
     */
    private function resolverEspacios(
        array $datos,
        ?TipoReserva $tipo,
    ): array {
        if ($tipo === TipoReserva::RESTAURANTE) {
            return [];
        }

        try {
            $espacios = $this->adicionales->resolverEspacios(
                $this->arrayDato(
                    $datos,
                    'espacios_adicionales',
                ),
            );

            return $this->normalizarConceptos(
                $espacios,
            );
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * Normaliza los conceptos provenientes de la capa
     * de validación para trabajar internamente con tipos conocidos.
     *
     *
     * @return array<int, array{
     *     precio: float,
     *     cantidad: int
     * }>
     */
    private function normalizarConceptos(
        mixed $conceptos,
    ): array {
        if (! is_array($conceptos)) {
            return [];
        }

        $resultado = [];

        foreach ($conceptos as $concepto) {
            if (! is_array($concepto)) {
                continue;
            }

            $precio = $concepto['precio'] ?? null;
            $cantidad = $concepto['cantidad'] ?? null;

            if (
                ! is_numeric($precio)
                || ! is_numeric($cantidad)
            ) {
                continue;
            }

            $resultado[] = [
                'precio' => (float) $precio,
                'cantidad' => max(
                    1,
                    (int) $cantidad,
                ),
            ];
        }

        return $resultado;
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    private function id(
        array $datos,
        string $campo,
    ): ?int {
        $valor = $datos[$campo] ?? null;

        if (! is_numeric($valor)) {
            return null;
        }

        return (int) $valor;
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<int, mixed>
     */
    private function arrayDato(
        array $datos,
        string $campo,
    ): array {
        $valor = $datos[$campo] ?? null;

        if (! is_array($valor)) {
            return [];
        }

        return array_values($valor);
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    private function entero(
        array $datos,
        string $campo,
        int $default = 0,
    ): int {
        $valor = $datos[$campo] ?? null;

        if (is_int($valor)) {
            return $valor;
        }

        if (is_numeric($valor)) {
            return (int) $valor;
        }

        return $default;
    }

    private function fecha(
        mixed $valor,
        Carbon $default,
    ): Carbon {
        if (
            ! is_string($valor)
            && ! is_int($valor)
            && ! is_float($valor)
        ) {
            return $default;
        }

        try {
            return Carbon::parse($valor);
        } catch (Throwable) {
            return $default;
        }
    }

    private function descripcionDuracion(
        ?TipoReserva $tipo,
        int $unidades,
    ): string {
        return match ($tipo) {
            TipoReserva::HABITACION => $unidades.' noche(s)',

            TipoReserva::RESTAURANTE => $unidades.' hora(s)',

            default => $unidades.' unidad(es)',
        };
    }
}
