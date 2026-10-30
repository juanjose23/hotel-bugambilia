<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Tesoreria;

use App\Repository\Models\Cuentas\PagoCuenta;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Tesoreria\AbastecimientoCaja;
use App\Repository\Models\Tesoreria\Caja;
use App\Repository\Models\Tesoreria\MovimientoCaja;
use App\Repository\Models\Tesoreria\OperacionCobroCaja;
use App\Repository\Models\Tesoreria\SaldoSesionCaja;
use App\Repository\Models\Tesoreria\SesionCaja;
use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class CajaRepositorio
{
    public function usuario(int $id): User
    {
        return User::query()->findOrFail($id);
    }

    public function bloquearUsuario(int $id): User
    {
        return User::query()->lockForUpdate()->findOrFail($id);
    }

    public function bloquearCaja(int $id): Caja
    {
        return Caja::query()->with('monedas')->lockForUpdate()->findOrFail($id);
    }

    public function bloquearSesion(int $id): SesionCaja
    {
        return SesionCaja::query()->lockForUpdate()->findOrFail($id);
    }

    public function activaUsuario(int $id): ?SesionCaja
    {
        return SesionCaja::query()->where('usuario_id', $id)->whereIn('estado', ['abierta', 'pendiente_revision'])->first();
    }

    public function activaCaja(int $id): ?SesionCaja
    {
        return SesionCaja::query()->where('caja_id', $id)->whereIn('estado', ['abierta', 'pendiente_revision'])->first();
    }

    public function porClave(string $clave): ?SesionCaja
    {
        return SesionCaja::query()->where('codigo', $clave)->first();
    }

    public function movimientoPorClave(string $clave): ?MovimientoCaja
    {
        return MovimientoCaja::query()->where('clave_operacion', $clave)->first();
    }

    /** @param list<int> $ids */
    public function monedasValidas(array $ids): bool
    {
        return Moneda::query()->whereIn('id', $ids)->count() === count($ids);
    }

    public function codigoExiste(string $codigo): bool
    {
        return Caja::query()->where('codigo', $codigo)->exists();
    }

    /**
     * @param  array<string,mixed>  $data
     * @param  list<int>  $monedas
     */
    public function crearCaja(array $data, array $monedas): Caja
    {
        $caja = Caja::query()->create($data);
        $caja->monedas()->attach($monedas);

        return $caja;
    }

    /** @param array<string,mixed> $data */
    public function crearSesion(array $data): SesionCaja
    {
        return SesionCaja::query()->create($data);
    }

    /** @param array<string,mixed> $data */
    public function crearSaldo(SesionCaja $sesion, array $data): void
    {
        $sesion->saldos()->create($data);
    }

    /** @param array<string,mixed> $data */
    public function crearMovimiento(array $data): MovimientoCaja
    {
        return MovimientoCaja::query()->create($data);
    }

    /** @return Collection<int,SaldoSesionCaja> */
    public function saldos(SesionCaja $s): Collection
    {
        return $s->saldos()->with('moneda')->orderBy('moneda_id')->get();
    }

    public function saldo(SesionCaja $s, int $monedaId): ?SaldoSesionCaja
    {
        return $s->saldos()->where('moneda_id', $monedaId)->first();
    }

    public function esperado(SesionCaja $s, SaldoSesionCaja $saldo): float
    {
        $centavos = (int) round((float) $saldo->apertura * 100);
        foreach ($s->movimientos()->where('moneda_id', $saldo->moneda_id)->get() as $m) {
            $cantidad = (int) round((float) $m->importe * 100);
            $centavos += in_array($m->tipo, ['ingreso', 'cobro'], true) ? $cantidad : -$cantidad;
        }

        return $centavos / 100;
    }

    /** @param array<string,mixed> $data */
    public function actualizarSesion(SesionCaja $s, array $data): SesionCaja
    {
        $s->update($data);

        return $s->refresh();
    }

    /** @param array<string,mixed> $data */
    public function guardarConteo(SaldoSesionCaja $s, array $data): void
    {
        $s->update($data);
    }

    /** @param list<int>|null $ids */
    public function cuentaTieneEfectivoRegistrado(int $cuentaId, ?array $ids = null): bool
    {
        return MovimientoCaja::query()->whereIn('pago_cuenta_id', PagoCuenta::query()->select('id')->where('cuenta_id', $cuentaId)->when($ids !== null, fn ($q) => $q->whereIn('id', $ids ?? [])))->exists();
    }

    public function pagoTieneEfectivoRegistrado(int $pagoId): bool
    {
        return MovimientoCaja::query()->where('pago_cuenta_id', $pagoId)->exists();
    }

    public function operacionCobro(string $clave): ?OperacionCobroCaja
    {
        return OperacionCobroCaja::query()->with('venta')->where('clave', $clave)->first();
    }

    /** @param array<string,mixed> $data */
    public function guardarOperacionCobro(array $data): void
    {
        OperacionCobroCaja::query()->create($data);
    }

    public function abastecimientoPendiente(int $sesion): bool
    {
        return AbastecimientoCaja::query()->where('sesion_id', $sesion)->where('estado', 'pendiente')->exists();
    }
}
