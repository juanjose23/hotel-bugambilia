<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Cuentas;

use App\Enums\Cuentas\TipoCargo;
use App\Repository\Models\Cuentas\CargoFacturacion;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\CuentaCargo;
use App\Repository\Models\Cuentas\CuentaDetalle;
use App\Repository\Models\Cuentas\PagoCuenta;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Restaurante\Pedido;
use Illuminate\Support\Collection;

interface CuentaRepositorioInterface
{
    /**
     * @param  array<string, mixed>  $datos
     */
    public function abrir(Cuenta $cuenta, array $datos): Cuenta;

    /** @param array<string, mixed> $datos */
    public function crear(array $datos): Cuenta;

    /** @param array<string, mixed> $datos */
    public function actualizar(Cuenta $cuenta, array $datos): Cuenta;

    public function refrescar(Cuenta $cuenta): Cuenta;

    public function bloquear(int $cuentaId): Cuenta;

    public function subtotalDetallesActivos(Cuenta $cuenta): float;

    public function sumaCargosActivos(Cuenta $cuenta, TipoCargo $tipo): float;

    public function sumaPagosAplicados(Cuenta $cuenta): float;

    /** @return Collection<int, CargoFacturacion> */
    public function cargosFacturacionObligatorios(): Collection;

    public function cuentaCargoVigente(Cuenta $cuenta, int $cargoId): ?CuentaCargo;

    public function descuentoManualDeCuenta(Cuenta $cuenta): ?CuentaCargo;

    public function cuentaCargoPorCodigo(Cuenta $cuenta, string $codigo): ?CuentaCargo;

    /** @param array<string, mixed> $datos */
    public function actualizarCuentaCargo(CuentaCargo $cuentaCargo, array $datos): void;

    /** @param array<string, mixed> $datos */
    public function crearCuentaCargo(Cuenta $cuenta, array $datos): CuentaCargo;

    /** @param array<string, mixed> $datos */
    public function crearDetalle(Cuenta $cuenta, array $datos): CuentaDetalle;

    public function detalleActivoConOrigen(Cuenta $cuenta, string $origenType, int $origenId): ?CuentaDetalle;

    /** @param array<string, mixed> $datos */
    public function actualizarDetalle(CuentaDetalle $detalle, array $datos): CuentaDetalle;

    /** @param array<string, mixed> $datos */
    public function crearPago(Cuenta $cuenta, array $datos): PagoCuenta;

    /** @param array<string, mixed> $datos */
    public function actualizarPago(PagoCuenta $pago, array $datos): PagoCuenta;

    /**
     * @param  list<int>|null  $pagoCuentaIds
     * @return Collection<int, PagoCuenta>
     */
    public function pagosAplicadosDeCuenta(Cuenta $cuenta, ?array $pagoCuentaIds = null): Collection;

    public function existeDetalleConOrigen(Cuenta $cuenta, string $origenType, int $origenId): bool;

    /** @return Collection<int, CuentaDetalle> */
    public function detallesActivos(Cuenta $cuenta): Collection;

    public function cargarPedidoEnCuenta(Pedido $pedido, int $cuentaId): void;

    public function pedidoConItemsCargados(Pedido $pedido): Pedido;

    public function monedaPredeterminada(): ?Moneda;

    /** @return Collection<int, CargoFacturacion> */
    public function cargosFacturacionActivos(): Collection;

    public function totalCargosObligatorios(Cuenta $cuenta): float;

    public function pedidoClienteDeCuenta(int $cuentaId): ?Pedido;

    /** @return Collection<int, Cuenta> */
    public function cuentasAbiertasDeReserva(int $reservaId): Collection;

    public function cuentaDeEstanciaOReservaConLock(int $estanciaId, int $reservaId): ?Cuenta;

    /** @return Collection<int, Cuenta> */
    public function cuentasCerradasDeReservaExcluyendo(int $reservaId, int $cuentaExcluidaId): Collection;

    public function primeraCuentaDeReserva(int $reservaId): ?Cuenta;

    /** @return Collection<int, int> */
    public function cargosFacturacionVigentesIds(Cuenta $cuenta): Collection;

    /** @param array<int, array<string, mixed>> $registros */
    public function insertarCuentaCargos(Cuenta $cuenta, array $registros): void;
}
