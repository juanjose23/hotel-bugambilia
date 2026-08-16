<?php

declare(strict_types=1);

namespace App\Repository\Queries\Facturacion;

use App\Enums\Facturacion\EstadoTransaccionPago;
use App\Enums\Facturacion\PasarelaCodigo;
use App\Repository\Models\Facturacion\PagoTransaccion;
use App\Repository\Models\Reservas\Reserva;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

final readonly class PagoTransaccionQuery
{
    public function porIdempotenciaKey(string $idempotencyKey): ?PagoTransaccion
    {
        /** @var PagoTransaccion|null $transaccion */
        $transaccion = PagoTransaccion::query()
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        return $transaccion;
    }

    /**
     * @param  list<string>  $with
     */
    public function porReferenciaPasarela(string $referencia, array $with = [], bool $lock = false): ?PagoTransaccion
    {
        $query = PagoTransaccion::query()
            ->where('referencia_pasarela', $referencia);

        if ($with !== []) {
            $query->with($with);
        }

        if ($lock) {
            $query->lockForUpdate();
        }

        /** @var PagoTransaccion|null $transaccion */
        $transaccion = $query->first();

        return $transaccion;
    }

    /**
     * @param  list<string>  $with
     */
    public function porReferenciaPasarelaRequerida(string $referencia, array $with = []): PagoTransaccion
    {
        return $this->porReferenciaPasarela($referencia, $with, lock: true)
            ?? throw (new ModelNotFoundException)->setModel(PagoTransaccion::class);
    }

    public function porReservaYReferencia(int $reservaId, string $referencia): ?PagoTransaccion
    {
        /** @var PagoTransaccion|null $transaccion */
        $transaccion = PagoTransaccion::query()
            ->where('referencia_pasarela', $referencia)
            ->where('reserva_id', $reservaId)
            ->first();

        return $transaccion;
    }

    /**
     * @param  list<string>  $with
     */
    public function porIdConLock(int $id, array $with = []): PagoTransaccion
    {
        $query = PagoTransaccion::query()->lockForUpdate();

        if ($with !== []) {
            $query->with($with);
        }

        /** @var PagoTransaccion $transaccion */
        $transaccion = $query->findOrFail($id);

        return $transaccion;
    }

    public function porReferenciaOPayload(string $texto): ?PagoTransaccion
    {
        /** @var PagoTransaccion|null $transaccion */
        $transaccion = PagoTransaccion::query()
            ->where('referencia_pasarela', $texto)
            ->orWhere('response_payload', 'like', "%{$texto}%")
            ->first();

        return $transaccion;
    }

    public function porPayloadConteniendo(string $texto): ?PagoTransaccion
    {
        /** @var PagoTransaccion|null $transaccion */
        $transaccion = PagoTransaccion::query()
            ->where('response_payload', 'like', "%{$texto}%")
            ->first();

        return $transaccion;
    }

    /**
     * @param  list<EstadoTransaccionPago>  $estados
     * @param  list<string>  $referenciasPrefijo
     * @return Collection<int, PagoTransaccion>
     */
    public function porReservaParaReembolso(
        Reserva $reserva,
        PasarelaCodigo $codigo,
        array $estados,
        bool $incluirTransaccionesDeCuenta = false,
        array $referenciasPrefijo = [],
    ): Collection {
        $cuentaIds = $incluirTransaccionesDeCuenta ? $reserva->cuentas()->pluck('id')->all() : [];

        $query = PagoTransaccion::query()
            ->with('moneda')
            ->where(function (Builder $builder) use ($reserva, $cuentaIds): void {
                $builder->where('reserva_id', $reserva->id);

                if ($cuentaIds !== []) {
                    $builder->orWhereIn('cuenta_id', $cuentaIds);
                }
            })
            ->whereIn('estado', array_map(
                fn (EstadoTransaccionPago $estado): int => $estado->value,
                $estados,
            ))
            ->whereNotNull('referencia_pasarela');

        if ($referenciasPrefijo !== []) {
            $query->where(function (Builder $builder) use ($codigo, $referenciasPrefijo): void {
                $builder->whereHas('pasarela', fn (Builder $pasarela): Builder => $pasarela->where('codigo', $codigo->value));

                foreach ($referenciasPrefijo as $prefijo) {
                    $builder->orWhere('referencia_pasarela', 'like', "{$prefijo}%");
                }
            });
        } else {
            $query->whereHas('pasarela', fn (Builder $pasarela): Builder => $pasarela->where('codigo', $codigo->value));
        }

        /** @var Collection<int, PagoTransaccion> $transacciones */
        $transacciones = $query
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        return $transacciones;
    }

    /**
     * @return Collection<int, PagoTransaccion>
     */
    public function porReservaConReferenciaPasarela(Reserva $reserva): Collection
    {
        /** @var Collection<int, PagoTransaccion> $transacciones */
        $transacciones = PagoTransaccion::query()
            ->with('moneda')
            ->where('reserva_id', $reserva->id)
            ->whereNotNull('referencia_pasarela')
            ->where('referencia_pasarela', '!=', '')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        return $transacciones;
    }
}
