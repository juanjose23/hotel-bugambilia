<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas\Resolutores;

use App\Enums\Reservas\TipoPagoReserva;
use App\Repository\Persistencia\Usuarios\ClientePersistencia;

/**
 * Resuelve el tipo de pago que debe aplicarse a la reserva.
 * Orden: tipo de cliente > tipo_pago_reserva explícito > registrar_abono > SIN_PAGO.
 */
final readonly class ResolverTipoPagoReserva
{
    public function __construct(
        private ClientePersistencia $clientePersistencia,
    ) {}

    /**
     * @param  array<string, mixed>  $datos
     */
    public function resolver(array $datos): TipoPagoReserva
    {
        $tipoPagoCliente = $this->resolverDesdeCliente($datos);

        if ($tipoPagoCliente !== null) {
            return $tipoPagoCliente;
        }

        $tipoPago = $datos['tipo_pago_reserva'] ?? null;

        if (is_string($tipoPago) && TipoPagoReserva::tryFrom($tipoPago) !== null) {
            return TipoPagoReserva::from($tipoPago);
        }

        return $datos['registrar_abono'] ?? false
            ? TipoPagoReserva::ABONO_50
            : TipoPagoReserva::SIN_PAGO;
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    private function resolverDesdeCliente(array $datos): ?TipoPagoReserva
    {
        $clienteId = $datos['cliente_id'] ?? null;

        if (! is_numeric($clienteId)) {
            return null;
        }

        $cliente = $this->clientePersistencia->buscarPorIdConTipo((int) $clienteId);

        $tipoCliente = $cliente?->tipoCliente;

        if ($tipoCliente === null) {
            return null;
        }

        foreach ([$tipoCliente->prefijo, $tipoCliente->descripcion, $tipoCliente->codigo] as $valor) {
            if (! is_string($valor) || trim($valor) === '') {
                continue;
            }

            $tipoPago = $this->extraerTipoPago($valor);

            if ($tipoPago !== null) {
                return $tipoPago;
            }
        }

        return null;
    }

    private function extraerTipoPago(string $valor): ?TipoPagoReserva
    {
        $normalizado = strtolower(trim($valor));

        if (str_contains($normalizado, '50') || str_contains($normalizado, 'mitad') || str_contains($normalizado, 'anticipo') || str_contains($normalizado, 'abono')) {
            return TipoPagoReserva::ABONO_50;
        }

        if (str_contains($normalizado, 'completo') || str_contains($normalizado, 'total') || str_contains($normalizado, '100')) {
            return TipoPagoReserva::PAGO_COMPLETO;
        }

        if (str_contains($normalizado, 'sin_pago') || str_contains($normalizado, 'libre') || str_contains($normalizado, 'gratis')) {
            return TipoPagoReserva::SIN_PAGO;
        }

        return null;
    }
}
