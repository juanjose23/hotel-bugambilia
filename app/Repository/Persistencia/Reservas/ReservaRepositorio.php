<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Reservas;

use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Repository\Models\Reservas\ReservaEstadoHistorial;
use App\Repository\Models\Reservas\ReservaHuesped;
use App\Repository\Models\Shared\Precio;
use DateTimeImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final readonly class ReservaRepositorio implements ReservaRepositorioInterface
{
    public function __construct(
        private RecursoReservableRepositorio $recursosRepo,
        private ReservaDetalleRepositorio $detallesRepo,
        private ReservaHuespedRepositorio $huespedesRepo,
        private EstanciaReservaRepositorio $estanciasRepo,
    ) {}

    public function obtenerPorId(int $id): ?Reserva
    {
        /** @var Reserva|null $reserva */
        $reserva = Reserva::query()->find($id);

        return $reserva;
    }

    public function buscarPorCodigoReserva(string $codigo): ?Reserva
    {
        /** @var Reserva|null $reserva */
        $reserva = Reserva::query()
            ->with(['cliente.persona.user', 'habitacion', 'espacio', 'servicio', 'promocion', 'moneda'])
            ->where('codigo_reserva', trim($codigo))
            ->first();

        return $reserva;
    }

    public function obtenerPorIdConLock(int $id): Reserva
    {
        return Reserva::query()->whereKey($id)->lockForUpdate()->firstOrFail();
    }

    public function obtenerPorIdConCuentasYMonedaConLock(int $id): ?Reserva
    {
        return Reserva::with(['cuentas.detalles', 'moneda'])->whereKey($id)->lockForUpdate()->first();
    }

    public function obtenerPorIdYCodigoConCuentasYMoneda(int $id, string $codigo): Reserva
    {
        /** @var Reserva $reserva */
        $reserva = Reserva::query()
            ->with(['cuentas', 'moneda'])
            ->where('id', $id)
            ->where('codigo_reserva', $codigo)
            ->firstOrFail();

        return $reserva;
    }

    public function obtenerDetalleConLock(int $detalleId): ReservaDetalle
    {
        return $this->detallesRepo->obtenerDetalleConLock($detalleId);
    }

    public function obtenerReservaDeDetalleConLock(ReservaDetalle $detalle): Reserva
    {
        return $this->detallesRepo->obtenerReservaDeDetalleConLock($detalle);
    }

    public function obtenerReservaDeEstanciaConLock(Estancia $estancia): Reserva
    {
        return $this->estanciasRepo->obtenerReservaDeEstanciaConLock($estancia);
    }

    public function obtenerDetalleDeEstanciaConLock(Estancia $estancia): ?ReservaDetalle
    {
        return $this->estanciasRepo->obtenerDetalleDeEstanciaConLock($estancia);
    }

    public function obtenerDetalleDeReservaConLock(int $reservaId): ReservaDetalle
    {
        return $this->detallesRepo->obtenerDetalleDeReservaConLock($reservaId);
    }

    public function estanciaConLock(int $estanciaId): Estancia
    {
        return $this->estanciasRepo->estanciaConLock($estanciaId);
    }

    public function existeEstanciaActivaParaDetalle(int $detalleId): bool
    {
        return $this->estanciasRepo->existeEstanciaActivaParaDetalle($detalleId);
    }

    public function tieneEstanciaActiva(Reserva $reserva): bool
    {
        return $this->estanciasRepo->tieneEstanciaActiva($reserva);
    }

    public function obtenerRecursoConLock(int $recursoId): RecursoReservable
    {
        return $this->recursosRepo->obtenerRecursoConLock($recursoId);
    }

    /** @return Collection<int, ReservaDetalle> */
    public function detallesDe(Reserva $reserva): Collection
    {
        return $this->detallesRepo->detallesDe($reserva);
    }

    /** @return Collection<int, ReservaDetalle> */
    public function detallesPrincipalesDe(Reserva $reserva): Collection
    {
        return $this->detallesRepo->detallesPrincipalesDe($reserva);
    }

    public function registrarHistorial(
        Reserva $reserva,
        ?EstadoReserva $anterior,
        EstadoReserva $nuevo,
        ?string $motivo = null,
        ?int $usuarioId = null,
    ): void {
        ReservaEstadoHistorial::query()->create([
            'reserva_id' => $reserva->id,
            'estado_anterior' => $anterior,
            'estado_nuevo' => $nuevo,
            'motivo' => $motivo,
            'usuario_id' => $usuarioId,
        ]);
    }

    /** @param array<int, int> $ids */
    public function bloquearRecursosReservables(array $ids): void
    {
        $this->recursosRepo->bloquearRecursosReservables($ids);
    }

    /** @param array<string, mixed> $datos */
    public function crear(array $datos): Reserva
    {
        /** @var Reserva $reserva */
        $reserva = Reserva::query()->create($datos);
        ReservaEstadoHistorial::query()->create([
            'reserva_id' => $reserva->id,
            'estado_anterior' => null,
            'estado_nuevo' => $reserva->estado,
            'motivo' => 'Reserva creada',
        ]);

        return $reserva;
    }

    /** @param array<string, mixed> $datos */
    public function actualizarDatosGenerales(Reserva $reserva, array $datos): Reserva
    {
        return DB::transaction(function () use ($reserva, $datos): Reserva {
            $bloqueada = Reserva::query()->lockForUpdate()->findOrFail($reserva->id);
            $bloqueada->update($datos);

            return $bloqueada->refresh();
        });
    }

    /** @param array<string, mixed> $datos */
    public function actualizar(Reserva $reserva, array $datos): Reserva
    {
        $reserva->update($datos);

        return $reserva->refresh();
    }

    public function resolverRecurso(TipoReserva $tipo, int $entidadId): RecursoReservable
    {
        return $this->recursosRepo->resolverRecurso($tipo, $entidadId);
    }

    /**
     * @param  array<int, array{tipo: TipoReserva, entidad_id: int}>  $solicitudes
     * @return array<int, RecursoReservable>
     */
    public function resolverRecursosLote(array $solicitudes): array
    {
        return $this->recursosRepo->resolverRecursosLote($solicitudes);
    }

    /** @param array<string, mixed> $datos */
    public function crearDetalle(Reserva $reserva, RecursoReservable $recurso, array $datos): ReservaDetalle
    {
        return $this->detallesRepo->crearDetalle($reserva, $recurso, $datos);
    }

    /** @param array<int, mixed> $huespedes */
    public function crearHuespedes(ReservaDetalle $detalle, array $huespedes): void
    {
        $this->huespedesRepo->crearHuespedes($detalle, $huespedes);
    }

    /**
     * @param  array<int, array{servicio_id: int, cantidad: int, precio: float}>  $servicios
     * @param  array<int, array{espacio_id: int, cantidad: int, precio: float}>  $espacios
     * @param  array<int, array{habitacion_id: int, cantidad: int, precio: float}>  $habitaciones
     */
    public function reemplazarAdicionales(
        Reserva $reserva,
        ReservaDetalle $principal,
        array $servicios,
        array $espacios,
        array $habitaciones = [],
    ): void {
        $this->detallesRepo->reemplazarAdicionales($reserva, $principal, $servicios, $espacios, $habitaciones);
    }

    /**
     * @param  array<int, RecursoReservable>  $recursos
     * @param  array<int, array{habitacion_id: int, precio: float}>  $habitaciones
     * @param  array<int, array{servicio_id: int, cantidad: int, precio: float}>  $servicios
     * @param  array<int, array{espacio_id: int, cantidad: int, precio: float}>  $espacios
     */
    public function crearDetallesAdicionales(
        Reserva $reserva,
        ReservaDetalle $principal,
        array $recursos,
        array $habitaciones,
        array $servicios,
        array $espacios,
        DateTimeImmutable $inicio,
        DateTimeImmutable $fin,
        int $unidades,
        ?float $horasVal,
    ): void {
        $this->detallesRepo->crearDetallesAdicionales(
            $reserva,
            $principal,
            $recursos,
            $habitaciones,
            $servicios,
            $espacios,
            $inicio,
            $fin,
            $unidades,
            $horasVal
        );
    }

    /** @param array<string, mixed> $datos */
    public function actualizarDetalle(ReservaDetalle $detalle, array $datos): ReservaDetalle
    {
        return $this->detallesRepo->actualizarDetalle($detalle, $datos);
    }

    public function detallePrincipalDe(Reserva $reserva): ReservaDetalle
    {
        return $this->detallesRepo->detallePrincipalDe($reserva);
    }

    /** @param array<string, mixed> $datos */
    public function crearHuesped(ReservaDetalle $detalle, array $datos): ReservaHuesped
    {
        return $this->huespedesRepo->crearHuesped($detalle, $datos);
    }

    /** @param array<string, mixed> $datos */
    public function actualizarHuesped(ReservaHuesped $huesped, array $datos): ReservaHuesped
    {
        return $this->huespedesRepo->actualizarHuesped($huesped, $datos);
    }

    public function eliminarHuesped(ReservaHuesped $huesped): void
    {
        $this->huespedesRepo->eliminarHuesped($huesped);
    }

    /** @param array<string, mixed> $datos */
    public function crearEstancia(array $datos): Estancia
    {
        return $this->estanciasRepo->crearEstancia($datos);
    }

    public function estanciaActivaDeReserva(Reserva $reserva): Estancia
    {
        return $this->estanciasRepo->estanciaActivaDeReserva($reserva);
    }

    /** @param array<string, mixed> $datos */
    public function actualizarEstancia(Estancia $estancia, array $datos): Estancia
    {
        return $this->estanciasRepo->actualizarEstancia($estancia, $datos);
    }

    public function cambiarEstado(Reserva $reserva, EstadoReserva $estado, ?int $usuarioId = null, ?string $motivo = null): void
    {
        DB::transaction(function () use ($reserva, $estado, $usuarioId, $motivo): void {
            $bloqueada = Reserva::query()->lockForUpdate()->findOrFail($reserva->id);
            $anterior = $bloqueada->estado;
            $bloqueada->update(['estado' => $estado]);

            $estadoDetalle = match ($estado) {
                EstadoReserva::PENDIENTE => EstadoReservaDetalle::PENDIENTE,
                EstadoReserva::CONFIRMADA => EstadoReservaDetalle::CONFIRMADO,
                EstadoReserva::PARCIALMENTE_CHECKED_IN => EstadoReservaDetalle::EN_USO,
                EstadoReserva::CHECKED_IN => EstadoReservaDetalle::EN_USO,
                EstadoReserva::PARCIALMENTE_CHECKED_OUT => EstadoReservaDetalle::EN_USO,
                EstadoReserva::CHECKED_OUT => EstadoReservaDetalle::COMPLETADO,
                EstadoReserva::CANCELADA => EstadoReservaDetalle::CANCELADO,
                EstadoReserva::NO_SHOW => EstadoReservaDetalle::CANCELADO,
            };
            $estadosOrigen = match ($estado) {
                EstadoReserva::PENDIENTE => [],
                EstadoReserva::CONFIRMADA => [EstadoReservaDetalle::PENDIENTE->value],
                EstadoReserva::PARCIALMENTE_CHECKED_IN => [],
                EstadoReserva::CHECKED_IN => [EstadoReservaDetalle::CONFIRMADO->value],
                EstadoReserva::PARCIALMENTE_CHECKED_OUT => [],
                EstadoReserva::CHECKED_OUT => [EstadoReservaDetalle::EN_USO->value],
                EstadoReserva::CANCELADA => [EstadoReservaDetalle::PENDIENTE->value, EstadoReservaDetalle::CONFIRMADO->value],
                EstadoReserva::NO_SHOW => [EstadoReservaDetalle::PENDIENTE->value, EstadoReservaDetalle::CONFIRMADO->value],
            };

            if ($estadosOrigen !== []) {
                $bloqueada->detalles()->whereIn('estado', $estadosOrigen)->update(['estado' => $estadoDetalle->value]);
            }

            ReservaEstadoHistorial::query()->create([
                'reserva_id' => $bloqueada->id,
                'estado_anterior' => $anterior,
                'estado_nuevo' => $estado,
                'motivo' => $motivo,
                'usuario_id' => $usuarioId,
            ]);

            $reserva->setRawAttributes($bloqueada->getAttributes(), true);
        });
    }

    public function duracionHorasActual(Reserva $reserva): ?int
    {
        return $this->detallesRepo->duracionHorasActual($reserva);
    }

    /** @return Collection<int, Reserva> */
    public function obtenerReservasRestauranteVencidas(string $fechaHoy, string $horaLimiteStr): Collection
    {
        return Reserva::query()
            ->where('tipo_reserva', TipoReserva::RESTAURANTE)
            ->whereIn('estado', [EstadoReserva::CONFIRMADA, EstadoReserva::PENDIENTE])
            ->whereDate('fecha_check_in', '<=', $fechaHoy)
            ->where(function ($q) use ($fechaHoy, $horaLimiteStr): void {
                $q->whereDate('fecha_check_in', '<', $fechaHoy)
                    ->orWhere(function ($q2) use ($horaLimiteStr): void {
                        $q2->whereNotNull('hora_reserva')
                            ->where('hora_reserva', '<=', $horaLimiteStr);
                    });
            })
            ->get();
    }

    public function buscarEstanciaConRelaciones(int $estanciaId): Estancia
    {
        /** @var Estancia $estancia */
        $estancia = Estancia::query()
            ->with(['reserva', 'cuenta', 'reservaDetalle'])
            ->findOrFail($estanciaId);

        return $estancia;
    }

    public function obtenerPrecioRecurso(RecursoReservable $recurso): float
    {
        $precioBruto = Precio::query()
            ->where('priceable_type', RecursoReservable::class)
            ->where('priceable_id', $recurso->id)
            ->value('precio');

        if (is_numeric($precioBruto)) {
            return (float) $precioBruto;
        }

        $habitacionPrecio = $recurso->habitacion?->precios()->first();
        if ($habitacionPrecio !== null) {
            return (float) $habitacionPrecio->precio;
        }

        $espacioPrecio = $recurso->espacio?->precios()->first();
        if ($espacioPrecio !== null) {
            return (float) $espacioPrecio->precio;
        }

        $servicioPrecio = $recurso->servicio?->precios()->first();
        if ($servicioPrecio !== null) {
            return (float) $servicioPrecio->precio;
        }

        return 0.0;
    }

    /** @param array<string, mixed> $datos */
    public function crearDetalleDirecto(array $datos): ReservaDetalle
    {
        /** @var ReservaDetalle $detalle */
        $detalle = ReservaDetalle::query()->create($datos);

        return $detalle;
    }
}
