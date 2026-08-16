<?php

declare(strict_types=1);

namespace App\Presenters\Cuentas;

use App\Enums\Cuentas\TipoCuenta;
use App\Repository\Models\Cuentas\CargoFacturacion;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\CuentaCargo;
use App\Repository\Models\Cuentas\CuentaDetalle;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
use App\Repository\Queries\Shared\ObtenerNombrePersona;

/**
 * Prepara la información de presentación de una Cuenta para el modal de cobro.
 * Determina el nombre del cliente, origen del consumo y fotografía financiera
 * según el tipo de cuenta (Estancia, Restaurante, Servicio).
 */
final class ResumenCuentaPresenter
{
    public function __construct(
        private readonly CuentaRepositorioInterface $cuentas,
        private readonly ObtenerNombrePersona $nombrePersona,
    ) {}

    /**
     * Devuelve un array apto para fillForm() en la CobrarCuentaAction.
     *
     * @return array<string, mixed>
     */
    public function paraModal(Cuenta $cuenta): array
    {
        $cuenta->loadMissing([
            'cliente.personaNatural',
            'cliente.personaJuridica',
            'estancia.habitacion',
            'estancia.reserva',
            'reserva',
            'moneda',
            'detalles',
            'cargos.cargoCatalogo',
        ]);

        $monedaCuenta = $cuenta->moneda ?? $this->cuentas->monedaPredeterminada();

        $cargosVigentes = $this->cuentas->cargosFacturacionActivos()
            ->map(fn (CargoFacturacion $c): array => [
                'id' => $c->id,
                'codigo' => $c->codigo,
                'nombre' => $c->nombre,
                'tipo_label' => $c->tipo->getLabel(),
                'valor' => (float) $c->valor,
                'modo_calculo' => $c->modo_calculo->getLabel(),
                'es_obligatorio' => (bool) $c->obligatorio,
            ])
            ->values()
            ->all();

        return [
            // Identificación
            'numero_cuenta' => $cuenta->numero_cuenta,
            'tipo_cuenta_label' => $cuenta->tipo_cuenta->getLabel(),
            'estado_label' => $cuenta->estado->getLabel(),
            'moneda_id' => $monedaCuenta?->id,
            'moneda_simbolo' => $monedaCuenta !== null ? $monedaCuenta->simbolo : 'C$',
            'moneda_codigo' => $monedaCuenta !== null ? $monedaCuenta->codigo : 'NIO',

            // Cliente resuelto según tipo de cuenta
            'nombre_cliente' => $this->resolverNombreCliente($cuenta),
            'telefono_cliente' => $this->resolverTelefonoCliente($cuenta),

            // Origen del consumo
            'origen_descripcion' => $this->resolverOrigenDescripcion($cuenta),

            // Consumos / Detalle de la cuenta
            'detalles' => $cuenta->detalles
                ->filter(fn (CuentaDetalle $d): bool => (int) $d->estado !== 0)
                ->map(fn (CuentaDetalle $d): array => [
                    'concepto' => $d->concepto,
                    'descripcion' => $d->descripcion,
                    'cantidad' => (float) $d->cantidad,
                    'precio_unitario' => (float) $d->precio_unitario,
                    'subtotal' => (float) $d->subtotal,
                    'total' => (float) $d->total,
                ])
                ->values()
                ->all(),

            // Cargos aplicados a esta cuenta
            'cargos' => $cuenta->cargos
                ->map(fn (CuentaCargo $c): array => [
                    'nombre' => $c->nombre,
                    'tipo_label' => $c->tipo->getLabel(),
                    'monto' => (float) $c->monto,
                    'es_obligatorio' => $c->cargoCatalogo !== null ? (bool) $c->cargoCatalogo->obligatorio : true,
                ])
                ->values()
                ->all(),

            // Cargos de facturación vigentes en catálogo
            'cargos_vigentes' => $cargosVigentes,

            // Financiero (read-only en el modal)
            'subtotal' => (float) $cuenta->subtotal,
            'descuento_total' => (float) $cuenta->descuento_total,
            'impuesto_total' => (float) $cuenta->impuesto_total,
            'cargo_servicio_total' => (float) $cuenta->cargo_servicio_total,
            'propina_total' => (float) $cuenta->propina_total,
            'recargo_total' => (float) $cuenta->recargo_total,
            'total' => (float) $cuenta->total,
            'total_pagado' => (float) $cuenta->total_pagado,
            'saldo' => (float) $cuenta->saldo,

            // Campo editable precargado con el saldo
            'monto' => (float) $cuenta->saldo > 0 ? (float) $cuenta->saldo : 0.0,
        ];
    }

    private function resolverNombreCliente(Cuenta $cuenta): string
    {
        // 1. Cliente directo de la cuenta
        if ($cuenta->cliente !== null && $cuenta->cliente->persona !== null) {
            $nombre = $this->nombrePersona->ejecutar($cuenta->cliente->persona);
            if (! empty($nombre)) {
                return $nombre;
            }
        }

        // 2. Si es cuenta de restaurante, buscar cliente asignado a la comanda
        $pedido = $this->cuentas->pedidoClienteDeCuenta($cuenta->id);

        if ($pedido !== null && $pedido->cliente !== null && $pedido->cliente->persona !== null) {
            $nombre = $this->nombrePersona->ejecutar($pedido->cliente->persona);
            if (! empty($nombre)) {
                return $nombre;
            }
        }

        // 3. Para estancias: buscar en la reserva vinculada
        if ($cuenta->tipo_cuenta === TipoCuenta::ESTANCIA) {
            $reserva = $cuenta->estancia !== null ? $cuenta->estancia->reserva : $cuenta->reserva;
            if ($reserva !== null && $reserva->nombre_cliente !== null) {
                return $reserva->nombre_cliente;
            }
        }

        return 'Cliente de mostrador';
    }

    private function resolverTelefonoCliente(Cuenta $cuenta): ?string
    {
        if ($cuenta->cliente !== null && filled($cuenta->cliente->telefono)) {
            return $cuenta->cliente->telefono;
        }

        if ($cuenta->tipo_cuenta === TipoCuenta::ESTANCIA) {
            $reserva = $cuenta->estancia !== null ? $cuenta->estancia->reserva : $cuenta->reserva;

            return $reserva !== null ? $reserva->telefono_cliente : null;
        }

        return null;
    }

    private function resolverOrigenDescripcion(Cuenta $cuenta): string
    {
        return match ($cuenta->tipo_cuenta) {
            TipoCuenta::ESTANCIA => $this->origenEstancia($cuenta),
            TipoCuenta::RESTAURANTE_DIRECTO => 'Restaurante · Consumo directo en mesa',
            TipoCuenta::SERVICIO => 'Servicio complementario',
        };
    }

    private function origenEstancia(Cuenta $cuenta): string
    {
        $partes = [];

        $habitacion = $cuenta->estancia?->habitacion?->nombre;
        if ($habitacion !== null) {
            $partes[] = "Habitación {$habitacion}";
        }

        $reserva = $cuenta->estancia !== null ? $cuenta->estancia->reserva : $cuenta->reserva;
        if ($reserva !== null && $reserva->fecha_check_in !== null && $reserva->fecha_check_out !== null) {
            $checkIn = $reserva->fecha_check_in->format('d/m/Y');
            $checkOut = $reserva->fecha_check_out->format('d/m/Y');
            $partes[] = "{$checkIn} → {$checkOut}";
        }

        return filled($partes) ? implode(' · ', $partes) : 'Estancia de huésped';
    }
}
