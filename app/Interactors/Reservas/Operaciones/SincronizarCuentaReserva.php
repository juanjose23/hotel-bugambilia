<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Operaciones;

use App\BusinessLogic\Monedas\ConvertirMoneda;
use App\Enums\Cuentas\BaseCalculo;
use App\Enums\Cuentas\ModoCargo;
use App\Enums\Cuentas\TipoCargo;
use App\Enums\Cuentas\TipoCuenta;
use App\Enums\Reservas\TipoReserva;
use App\Enums\Shared\EstadoGeneral;
use App\Interactors\Cuentas\Gestion\AbrirCuenta;
use App\Interactors\Cuentas\Gestion\RecalcularCuenta;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use Illuminate\Support\Collection;

final class SincronizarCuentaReserva
{
    public function __construct(
        private readonly CuentaRepositorioInterface $cuentas,
        private readonly ReservaRepositorioInterface $reservas,
        private readonly RecalcularCuenta $recalcularCuenta,
        private readonly ConvertirMoneda $convertirMoneda,
        private readonly AbrirCuenta $abrirCuenta,
    ) {}

    public function ejecutar(Reserva $reserva, Cuenta $cuenta, ?int $usuarioId = null): Reserva
    {
        if (! $cuenta->estado->permiteNuevosCargos()) {
            $tipoCuenta = match ($reserva->tipo_reserva) {
                TipoReserva::HABITACION => TipoCuenta::ESTANCIA,
                TipoReserva::RESTAURANTE => TipoCuenta::RESTAURANTE_DIRECTO,
                TipoReserva::SERVICIO, TipoReserva::PAQUETE => TipoCuenta::SERVICIO,
            };

            $cuenta = $this->abrirCuenta->ejecutar(
                tipo: $tipoCuenta,
                reserva: $reserva,
                cliente: $reserva->cliente,
                monedaId: $reserva->moneda_id,
                usuarioId: $usuarioId,
            );
        }

        $cuentasCerradas = $this->cuentas->cuentasCerradasDeReservaExcluyendo((int) $reserva->id, (int) $cuenta->id);

        $subtotalCerradoBase = $this->sumaFlotante($cuentasCerradas, 'subtotal');
        $descuentoCerradoBase = $this->sumaFlotante($cuentasCerradas, 'descuento_total');
        $totalCerradoBase = $this->sumaFlotante($cuentasCerradas, 'total');
        $totalPagadoCerradoBase = $this->sumaFlotante($cuentasCerradas, 'total_pagado');

        $subtotalAjustadoBase = max(0.0, (float) $reserva->subtotal - $subtotalCerradoBase);
        $descuentoAjustadoBase = max(0.0, (float) $reserva->descuento - $descuentoCerradoBase);

        $subtotal = $this->convertirMoneda->desdeBase($subtotalAjustadoBase, (int) $cuenta->moneda_id);
        $descuento = $this->convertirMoneda->desdeBase($descuentoAjustadoBase, (int) $cuenta->moneda_id);
        $detalle = $this->cuentas->detalleActivoConOrigen($cuenta, $reserva->getMorphClass(), (int) $reserva->id);

        $datosDetalle = [
            'moneda_id' => $cuenta->moneda_id,
            'origen_type' => $reserva->getMorphClass(),
            'origen_id' => $reserva->id,
            'espacio_id' => $reserva->espacio_id,
            'concepto' => "Reserva {$reserva->codigo_reserva}",
            'descripcion' => $reserva->tipo_reserva->getLabel(),
            'cantidad' => 1,
            'precio_unitario' => $subtotal,
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'estado' => EstadoGeneral::Activo->value,
            'metadatos' => ['tipo' => 'reserva', 'codigo_reserva' => $reserva->codigo_reserva],
            'creador_id' => $usuarioId,
        ];

        if ($detalle === null) {
            $this->cuentas->crearDetalle($cuenta, $datosDetalle);
        } else {
            $this->cuentas->actualizarDetalle($detalle, $datosDetalle);
        }

        $this->sincronizarDescuento($reserva, $cuenta, $descuento, $subtotal, $usuarioId);

        $cuenta = $this->recalcularCuenta->ejecutar($cuenta, $usuarioId);
        $subtotalReservaTotal = round((float) $cuenta->subtotal + $subtotalCerradoBase, 2);
        $totalReservaTotal = round((float) $cuenta->total + $totalCerradoBase, 2);
        $totalPagadoReservaTotal = round(max((float) $reserva->total_pagado, (float) $cuenta->total_pagado + $totalPagadoCerradoBase), 2);
        $saldoReservaTotal = round(max(0.0, $totalReservaTotal - $totalPagadoReservaTotal), 2);

        return $this->reservas->actualizar($reserva, [
            'subtotal' => $subtotalReservaTotal,
            'total' => $totalReservaTotal,
            'total_pagado' => $totalPagadoReservaTotal,
            'saldo' => $saldoReservaTotal,
        ]);
    }

    /**
     * @param  Collection<int, Cuenta>  $cuentas
     */
    private function sumaFlotante(Collection $cuentas, string $columna): float
    {
        $suma = $cuentas->sum($columna);

        return is_numeric($suma) ? (float) $suma : 0.0;
    }

    private function sincronizarDescuento(Reserva $reserva, Cuenta $cuenta, float $descuento, float $subtotal, ?int $usuarioId): void
    {
        $codigo = "PROMO-RES-{$reserva->id}";
        $cargo = $this->cuentas->cuentaCargoPorCodigo($cuenta, $codigo);

        if ($descuento <= 0) {
            if ($cargo !== null) {
                $this->cuentas->actualizarCuentaCargo($cargo, ['estado' => EstadoGeneral::Inactivo->value]);
            }

            return;
        }

        $datos = [
            'moneda_id' => $cuenta->moneda_id,
            'cargo_id' => null,
            'tipo' => TipoCargo::Descuento->value,
            'codigo' => $codigo,
            'nombre' => 'Descuento de reserva',
            'modo_calculo' => ModoCargo::MontoFijo->value,
            'valor' => $descuento,
            'base_calculo' => BaseCalculo::SubtotalBruto->value,
            'base_monto' => $subtotal,
            'monto' => $descuento,
            'aplicado_por' => $usuarioId,
            'estado' => EstadoGeneral::Activo->value,
            'observaciones' => 'Descuento sincronizado al editar la reserva',
        ];

        if ($cargo === null) {
            $this->cuentas->crearCuentaCargo($cuenta, $datos);

            return;
        }

        $this->cuentas->actualizarCuentaCargo($cargo, $datos);
    }
}
