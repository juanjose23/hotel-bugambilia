<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas;

use App\Repository\Queries\Reservas\ObtenerTarifasReservaQuery;
use InvalidArgumentException;

final class ValidarSeleccionAdicionales
{
    public function __construct(
        private readonly ObtenerTarifasReservaQuery $tarifas,
    ) {}

    /**
     * @param  array<int, mixed>  $solicitados
     * @return array<int, array{servicio_id: int, cantidad: int, precio: float}>
     */
    public function resolverServicios(array $solicitados, ?int $servicioPrincipalId = null): array
    {
        $servicios = [];

        foreach ($solicitados as $solicitado) {
            if (! is_array($solicitado)) {
                throw new InvalidArgumentException('Los servicios adicionales no son válidos.');
            }

            $servicioId = $this->enteroRequerido($solicitado, 'servicio_id');

            if ($servicioId === $servicioPrincipalId) {
                throw new InvalidArgumentException('El servicio principal no puede agregarse nuevamente como adicional.');
            }

            $cantidadValor = $solicitado['cantidad'] ?? 1;
            $cantidad = is_int($cantidadValor)
                ? $cantidadValor
                : (is_string($cantidadValor) && ctype_digit($cantidadValor) ? (int) $cantidadValor : 1);

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
            if (! is_array($solicitado)) {
                throw new InvalidArgumentException('Los espacios adicionales no son válidos.');
            }

            $espacioId = $this->enteroRequerido($solicitado, 'espacio_id');

            if ($espacioId === $espacioPrincipalId) {
                throw new InvalidArgumentException('El espacio principal no puede agregarse nuevamente como adicional.');
            }

            $cantidadValor = $solicitado['cantidad'] ?? 1;
            $cantidad = is_int($cantidadValor)
                ? $cantidadValor
                : (is_string($cantidadValor) && ctype_digit($cantidadValor) ? (int) $cantidadValor : 1);

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

    /** @param array<string|int, mixed> $datos */
    private function enteroRequerido(array $datos, string $campo): int
    {
        $valor = $datos[$campo] ?? null;

        if (is_int($valor)) {
            return $valor;
        }

        if (is_string($valor) && ctype_digit($valor)) {
            return (int) $valor;
        }

        throw new InvalidArgumentException("El campo {$campo} no es válido.");
    }
}
