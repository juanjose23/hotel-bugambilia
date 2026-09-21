<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas\Validaciones;

use App\BusinessLogic\Reservas\Data\EspacioAdicionalItemData;
use App\BusinessLogic\Reservas\Data\HabitacionAdicionalItemData;
use App\BusinessLogic\Reservas\Data\ServicioAdicionalItemData;
use App\Repository\Queries\Reservas\ObtenerTarifasReservaQuery;
use InvalidArgumentException;

final readonly class ValidarSeleccionAdicionales
{
    public function __construct(
        private ObtenerTarifasReservaQuery $tarifas,
    ) {}

    /**
     * @param  array<int, mixed>  $solicitados
     * @return array<int, array{servicio_id: int, cantidad: int, precio: float}>
     */
    public function resolverServicios(array $solicitados, ?int $servicioPrincipalId = null): array
    {
        $servicios = [];

        foreach ($solicitados as $solicitado) {
            if ($solicitado instanceof ServicioAdicionalItemData) {
                $servicioId = $solicitado->servicioId;
                $cantidad = $solicitado->cantidad;
            } elseif (is_array($solicitado)) {
                $servicioId = $this->enteroRequerido($solicitado, 'servicio_id');
                $cantidadValor = $solicitado['cantidad'] ?? 1;
                $cantidad = is_int($cantidadValor)
                    ? $cantidadValor
                    : (is_string($cantidadValor) && ctype_digit($cantidadValor) ? (int) $cantidadValor : 1);
            } else {
                throw new InvalidArgumentException('Los servicios adicionales no son válidos.');
            }

            if ($servicioId === $servicioPrincipalId) {
                throw new InvalidArgumentException('El servicio principal no puede agregarse nuevamente como adicional.');
            }

            $servicios[] = [
                'servicio_id' => $servicioId,
                'cantidad' => $cantidad,
                'precio' => $this->tarifas->servicio($servicioId),
            ];
        }

        return $servicios;
    }

    /**
     * @param  array<int, mixed>  $solicitados
     * @return array<int, array{espacio_id: int, cantidad: int, precio: float}>
     */
    public function resolverEspacios(array $solicitados, ?int $espacioPrincipalId = null): array
    {
        $espacios = [];

        foreach ($solicitados as $solicitado) {
            if ($solicitado instanceof EspacioAdicionalItemData) {
                $espacioId = $solicitado->espacioId;
                $cantidad = $solicitado->cantidad;
            } elseif (is_array($solicitado)) {
                $espacioId = $this->enteroRequerido($solicitado, 'espacio_id');
                $cantidadValor = $solicitado['cantidad'] ?? 1;
                $cantidad = is_int($cantidadValor)
                    ? $cantidadValor
                    : (is_string($cantidadValor) && ctype_digit($cantidadValor) ? (int) $cantidadValor : 1);
            } else {
                throw new InvalidArgumentException('Los espacios adicionales no son válidos.');
            }

            if ($espacioId === $espacioPrincipalId) {
                throw new InvalidArgumentException('El espacio principal no puede agregarse nuevamente como adicional.');
            }

            if ($cantidad !== 1) {
                throw new InvalidArgumentException('Cada espacio físico debe agregarse una sola vez.');
            }

            $espacios[] = [
                'espacio_id' => $espacioId,
                'cantidad' => $cantidad,
                'precio' => $this->tarifas->espacio($espacioId),
            ];
        }

        return $espacios;
    }

    /**
     * @param  array<int, mixed>  $solicitados
     * @return array<int, array{habitacion_id: int, cantidad: int, precio: float}>
     */
    public function resolverHabitaciones(array $solicitados, ?int $habitacionPrincipalId = null): array
    {
        $habitaciones = [];

        foreach ($solicitados as $solicitado) {
            if ($solicitado instanceof HabitacionAdicionalItemData) {
                $habitacionId = $solicitado->habitacionId;
                $cantidad = $solicitado->cantidad;
            } elseif (is_array($solicitado)) {
                $habitacionId = $this->enteroRequerido($solicitado, 'habitacion_id');
                $cantidadValor = $solicitado['cantidad'] ?? 1;
                $cantidad = is_int($cantidadValor)
                    ? $cantidadValor
                    : (is_string($cantidadValor) && ctype_digit($cantidadValor) ? (int) $cantidadValor : 1);
            } else {
                throw new InvalidArgumentException('Las habitaciones adicionales no son válidas.');
            }

            if ($habitacionId === $habitacionPrincipalId) {
                throw new InvalidArgumentException('La habitación principal no puede agregarse nuevamente como adicional.');
            }

            if ($cantidad !== 1) {
                throw new InvalidArgumentException('Cada habitación adicional debe agregarse una sola vez.');
            }

            $habitaciones[] = [
                'habitacion_id' => $habitacionId,
                'cantidad' => $cantidad,
                'precio' => $this->tarifas->habitacion($habitacionId),
            ];
        }

        return $habitaciones;
    }

    /**
     * @param  array<array-key, mixed>  $item
     */
    private function enteroRequerido(array $item, string $campo): int
    {
        $valor = $item[$campo] ?? null;

        if (is_int($valor)) {
            return $valor;
        }

        if (is_string($valor) && ctype_digit($valor)) {
            return (int) $valor;
        }

        throw new InvalidArgumentException("El campo {$campo} del elemento adicional no es válido.");
    }
}
