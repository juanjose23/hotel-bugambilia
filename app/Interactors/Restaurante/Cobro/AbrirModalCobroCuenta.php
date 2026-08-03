<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cobro;

use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;

/**
 * DTO que contiene los datos calculados para abrir el modal de cobro de cuenta.
 */
final class DatosCobroCuenta
{
    public function __construct(
        public readonly int $cuentaId,
        public readonly string $numeroCuenta,
        public readonly string $estadoCuenta,
        public readonly string $tipoCuenta,
        public readonly float $subtotal,
        public readonly float $descuento,
        public readonly float $impuesto,
        public readonly float $servicio,
        public readonly float $propina,
        public readonly float $recargo,
        public readonly float $total,
        public readonly float $pagado,
        public readonly float $saldo,
        public readonly ?int $monedaId,
        public readonly string $simboloMoneda,
        public readonly ?int $clienteId,
        public readonly ?string $clienteNombre,
    ) {}
}

final class AbrirModalCobroCuenta
{
    public function __construct(
        private readonly RestauranteRepositorioInterface $repositorio,
    ) {}

    /**
     * Prepara los datos para el modal de cobro de una cuenta existente.
     *
     * @throws \DomainException si la cuenta no existe
     */
    public function ejecutar(int $cuentaId): DatosCobroCuenta
    {
        $cuenta = $this->repositorio->obtenerCuentaCobro($cuentaId);

        if ($cuenta === null) {
            throw new \DomainException('Cuenta no encontrada.');
        }

        $simboloMoneda = 'C$';
        if ($cuenta->moneda instanceof Moneda) {
            $simboloMoneda = $cuenta->moneda->simbolo ?? 'C$';
        }

        $nombreCliente = null;
        if ($cuenta->cliente !== null) {
            $nombreCliente = $cuenta->cliente->nombre_completo
                ?? $cuenta->cliente->primer_nombre;
        }

        return new DatosCobroCuenta(
            cuentaId: (int) $cuenta->id,
            numeroCuenta: $cuenta->numero_cuenta,
            estadoCuenta: $cuenta->estado->getLabel(),
            tipoCuenta: $cuenta->tipo_cuenta->getLabel(),
            subtotal: round((float) $cuenta->subtotal, 2),
            descuento: round((float) $cuenta->descuento_total, 2),
            impuesto: round((float) $cuenta->impuesto_total, 2),
            servicio: round((float) $cuenta->cargo_servicio_total, 2),
            propina: round((float) $cuenta->propina_total, 2),
            recargo: round((float) $cuenta->recargo_total, 2),
            total: round((float) $cuenta->total, 2),
            pagado: round((float) $cuenta->total_pagado, 2),
            saldo: round((float) $cuenta->saldo, 2),
            monedaId: $cuenta->moneda_id !== null ? (int) $cuenta->moneda_id : null,
            simboloMoneda: $simboloMoneda,
            clienteId: $cuenta->cliente_id,
            clienteNombre: $nombreCliente,
        );
    }
}
