<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas;

use App\Enums\Reservas\TipoPagoReserva;
use App\Repository\Models\Clientes\Cliente;

/**
 * Resuelve el tipo de pago que debe aplicarse a la reserva.
 * Orden: tipo de cliente > tipo_pago_reserva explícito > registrar_abono > SIN_PAGO.
 */
final readonly class ResolverTipoPagoReserva
{
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

        $cliente = Cliente::query()
            ->with('tipoCliente')
            ->find((int) $clienteId);

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
        $valor = trim(mb_strtolower($valor));
        $json = json_decode($valor, true);

        if (is_array($json)) {
            $configurado = $json['tipo_pago_reserva'] ?? $json['politica_pago'] ?? null;

            return is_string($configurado) ? TipoPagoReserva::tryFrom($configurado) : null;
        }

        return match ($valor) {
            'sin_pago', 'credito', 'corporativo_credito', 'cli_corporativo' => TipoPagoReserva::SIN_PAGO,
            'abono_50', '50', 'anticipo_50' => TipoPagoReserva::ABONO_50,
            'pago_completo', 'contado', 'prepago', 'vip_prepago' => TipoPagoReserva::PAGO_COMPLETO,
            default => TipoPagoReserva::tryFrom($valor),
        };
    }
}
