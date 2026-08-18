<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Reservas;

use App\Enums\Estancias\EstadoEstancia;
use App\Enums\Reservas\ControlDisponibilidad;
use App\Enums\Reservas\EstadoRecursoReservable;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\TipoHuesped;
use App\Enums\Reservas\TipoRecursoReservable;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Repository\Models\Reservas\ReservaEstadoHistorial;
use App\Repository\Models\Reservas\ReservaHuesped;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Queries\Reservas\ObtenerTarifasReservaQuery;
use DateTimeImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class ReservaRepositorio implements ReservaRepositorioInterface
{
    public function __construct(
        private readonly ObtenerTarifasReservaQuery $tarifas,
    ) {}

    public function obtenerPorId(int $id): ?Reserva
    {
        /** @var Reserva|null $reserva */
        $reserva = Reserva::query()->find($id);

        return $reserva;
    }

    public function obtenerPorIdConLock(int $id): Reserva
    {
        /** @var Reserva $reserva */
        $reserva = Reserva::query()->where('id', $id)->lockForUpdate()->firstOrFail();

        return $reserva;
    }

    public function obtenerDetalleConLock(int $detalleId): ReservaDetalle
    {
        /** @var ReservaDetalle $detalle */
        $detalle = ReservaDetalle::query()->where('id', $detalleId)->lockForUpdate()->firstOrFail();

        return $detalle;
    }

    public function obtenerReservaDeDetalleConLock(ReservaDetalle $detalle): Reserva
    {
        /** @var Reserva $reserva */
        $reserva = Reserva::query()->where('id', $detalle->reserva_id)->lockForUpdate()->firstOrFail();

        return $reserva;
    }

    public function obtenerReservaDeEstanciaConLock(Estancia $estancia): Reserva
    {
        /** @var Reserva $reserva */
        $reserva = Reserva::query()->where('id', $estancia->reserva_id)->lockForUpdate()->firstOrFail();

        return $reserva;
    }

    public function obtenerDetalleDeEstanciaConLock(Estancia $estancia): ?ReservaDetalle
    {
        /** @var ReservaDetalle|null $detalle */
        $detalle = ReservaDetalle::query()
            ->where('id', $estancia->reserva_detalle_id)
            ->lockForUpdate()
            ->first();

        return $detalle;
    }

    public function obtenerDetalleDeReservaConLock(int $reservaId): ReservaDetalle
    {
        /** @var ReservaDetalle $detalle */
        $detalle = ReservaDetalle::query()->where('reserva_id', $reservaId)->lockForUpdate()->firstOrFail();

        return $detalle;
    }

    public function estanciaConLock(int $estanciaId): Estancia
    {
        /** @var Estancia $estancia */
        $estancia = Estancia::query()->where('id', $estanciaId)->lockForUpdate()->firstOrFail();

        return $estancia;
    }

    public function existeEstanciaActivaParaDetalle(int $detalleId): bool
    {
        return Estancia::query()
            ->where('reserva_detalle_id', $detalleId)
            ->where('estado', EstadoEstancia::ACTIVA)
            ->exists();
    }

    public function tieneEstanciaActiva(Reserva $reserva): bool
    {
        return $reserva->estancias()
            ->where('estado', EstadoEstancia::ACTIVA)
            ->exists();
    }

    public function obtenerRecursoConLock(int $recursoId): RecursoReservable
    {
        /** @var RecursoReservable $recurso */
        $recurso = RecursoReservable::query()
            ->with('habitacion')
            ->where('id', $recursoId)
            ->lockForUpdate()
            ->firstOrFail();

        return $recurso;
    }

    /** @return Collection<int, ReservaDetalle> */
    public function detallesDe(Reserva $reserva): Collection
    {
        return $reserva->detalles()->get();
    }

    /** @return Collection<int, ReservaDetalle> */
    public function detallesPrincipalesDe(Reserva $reserva): Collection
    {
        return $reserva->detalles()->whereNull('parent_id')->get();
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
        RecursoReservable::query()
            ->whereIn('id', $ids)
            ->lockForUpdate()
            ->get();
    }

    public function crear(array $datos): Reserva
    {
        $reserva = Reserva::query()->create($datos);
        ReservaEstadoHistorial::query()->create([
            'reserva_id' => $reserva->id,
            'estado_anterior' => null,
            'estado_nuevo' => $reserva->estado,
            'motivo' => 'Reserva creada',
        ]);

        return $reserva;
    }

    public function actualizarDatosGenerales(Reserva $reserva, array $datos): Reserva
    {
        return DB::transaction(function () use ($reserva, $datos): Reserva {
            $bloqueada = Reserva::query()->lockForUpdate()->findOrFail($reserva->id);
            $bloqueada->update($datos);

            return $bloqueada->refresh();
        });
    }

    public function actualizar(Reserva $reserva, array $datos): Reserva
    {
        $reserva->update($datos);

        return $reserva->refresh();
    }

    public function adjuntarServicios(Reserva $reserva, array $servicios): void
    {
        foreach ($servicios as $servicio) {
            $reserva->serviciosAdicionales()->attach($servicio['servicio_id'], [
                'cantidad' => $servicio['cantidad'],
                'precio' => $servicio['precio'],
            ]);
        }
    }

    public function resolverRecurso(TipoReserva $tipo, int $entidadId): RecursoReservable
    {
        [$entidad, $tipoRecurso, $control, $nombre, $capacidad] = match ($tipo) {
            TipoReserva::HABITACION => $this->datosHabitacion($entidadId),
            TipoReserva::RESTAURANTE => $this->datosEspacio($entidadId),
            TipoReserva::SERVICIO => $this->datosServicio($entidadId),
            TipoReserva::PAQUETE => Habitacion::query()->find($entidadId) !== null
                ? $this->datosHabitacion($entidadId)
                : (Espacio::query()->find($entidadId) !== null
                    ? $this->datosEspacio($entidadId)
                    : $this->datosServicio($entidadId)),
        };

        if ($entidad->reservable_id !== null) {
            return RecursoReservable::query()->lockForUpdate()->findOrFail($entidad->reservable_id);
        }

        $recurso = RecursoReservable::query()->create([
            'tipo' => $tipoRecurso,
            'nombre' => $nombre,
            'capacidad' => $capacidad,
            'control_disponibilidad' => $control,
            'estado' => EstadoRecursoReservable::ACTIVO,
        ]);
        $entidad->update(['reservable_id' => $recurso->id]);

        return $recurso;
    }

    /**
     * Resuelve múltiples recursos reservables en lote, optimizando queries y locks.
     *
     * Estrategia:
     * 1. Agrupa entidades por tipo y ejecuta una query por tipo (Habitación, Espacio, Servicio)
     * 2. Para entidades existentes, obtiene el recurso asociado
     * 3. Para entidades nuevas, crea el recurso dentro de la misma transacción
     * 4. Utiliza bloqueo SELECT FOR UPDATE para prevenir condiciones de carrera
     *
     * @param  array<int, array{tipo: TipoReserva, entidad_id: int}>  $solicitudes
     * @return array<int, RecursoReservable> Indexadas por la clave original del array de entrada
     */
    public function resolverRecursosLote(array $solicitudes): array
    {
        if ($solicitudes === []) {
            return [];
        }

        $porTipo = [
            TipoReserva::HABITACION->value => [],
            TipoReserva::RESTAURANTE->value => [],
            TipoReserva::SERVICIO->value => [],
        ];

        foreach ($solicitudes as $idx => $solicitud) {
            $porTipo[$solicitud['tipo']->value][$idx] = $solicitud['entidad_id'];
        }

        $entidadesPorTipo = [];
        if ($porTipo[TipoReserva::HABITACION->value] !== []) {
            $ids = array_values($porTipo[TipoReserva::HABITACION->value]);
            $entidadesPorTipo[TipoReserva::HABITACION->value] = Habitacion::query()
                ->whereIn('id', $ids)
                ->get()
                ->keyBy('id');
        }
        if ($porTipo[TipoReserva::RESTAURANTE->value] !== []) {
            $ids = array_values($porTipo[TipoReserva::RESTAURANTE->value]);
            $entidadesPorTipo[TipoReserva::RESTAURANTE->value] = Espacio::query()
                ->whereIn('id', $ids)
                ->get()
                ->keyBy('id');
        }
        if ($porTipo[TipoReserva::SERVICIO->value] !== []) {
            $ids = array_values($porTipo[TipoReserva::SERVICIO->value]);
            $entidadesPorTipo[TipoReserva::SERVICIO->value] = Servicio::query()
                ->whereIn('id', $ids)
                ->get()
                ->keyBy('id');
        }

        $recursoIds = [];
        $entidadesIndexadas = [];

        foreach ($solicitudes as $idx => $solicitud) {
            $tipo = $solicitud['tipo'];
            $entidadId = $solicitud['entidad_id'];
            $entidad = $entidadesPorTipo[$tipo->value][$entidadId] ?? null;

            if ($entidad === null) {
                $modelo = match ($tipo) {
                    TipoReserva::HABITACION => new Habitacion,
                    TipoReserva::RESTAURANTE => new Espacio,
                    default => new Servicio,
                };
                $entidad = $modelo->newQuery()->lockForUpdate()->findOrFail($entidadId);
            }

            $entidadesIndexadas[$idx] = $entidad;

            if ($entidad->reservable_id !== null) {
                $recursoIds[$idx] = (int) $entidad->reservable_id;
            }
        }

        $recursosExistentes = $recursoIds !== []
            ? RecursoReservable::query()
                ->whereIn('id', array_values($recursoIds))
                ->lockForUpdate()
                ->get()
                ->keyBy('id')
            : collect();

        $resultados = [];
        $nuevosRecursos = [];

        foreach ($solicitudes as $idx => $solicitud) {
            $tipo = $solicitud['tipo'];
            $entidad = $entidadesIndexadas[$idx];

            if (isset($recursoIds[$idx])) {
                $resultados[$idx] = $recursosExistentes[$recursoIds[$idx]];
            } else {
                [$tipoRecurso, $control, $nombre, $capacidad] = match ($tipo) {
                    TipoReserva::HABITACION => [TipoRecursoReservable::HABITACION, ControlDisponibilidad::FECHAS, (string) $entidad->nombre, null],
                    TipoReserva::RESTAURANTE => [TipoRecursoReservable::ESPACIO, ControlDisponibilidad::HORARIO, (string) $entidad->nombre, $entidad instanceof Espacio ? (int) $entidad->capacidad_personas : null],
                    default => [TipoRecursoReservable::SERVICIO, ControlDisponibilidad::SIN_BLOQUEO, (string) $entidad->nombre, null],
                };

                $nuevosRecursos[$idx] = [
                    'tipo' => $tipoRecurso,
                    'nombre' => $nombre,
                    'capacidad' => $capacidad,
                    'control_disponibilidad' => $control,
                    'estado' => EstadoRecursoReservable::ACTIVO,
                    'entidad' => $entidad,
                ];
            }
        }

        foreach ($nuevosRecursos as $idx => $data) {
            $recurso = RecursoReservable::query()->create([
                'tipo' => $data['tipo'],
                'nombre' => $data['nombre'],
                'capacidad' => $data['capacidad'],
                'control_disponibilidad' => $data['control_disponibilidad'],
                'estado' => $data['estado'],
            ]);
            $data['entidad']->update(['reservable_id' => $recurso->id]);
            $resultados[$idx] = $recurso;
        }

        return $resultados;
    }

    public function crearDetalle(Reserva $reserva, RecursoReservable $recurso, array $datos): ReservaDetalle
    {
        return $reserva->detalles()->create([
            ...$datos,
            'reservable_id' => $recurso->id,
        ]);
    }

    public function crearHuespedes(ReservaDetalle $detalle, array $huespedes): void
    {
        $registros = [];
        $now = now();

        foreach ($huespedes as $huesped) {
            if (! is_array($huesped) || empty($huesped['nombre'])) {
                continue;
            }

            $tipoRaw = strtolower($this->aString($huesped['tipo'] ?? 'adulto', 'adulto'));
            $tipo = match ($tipoRaw) {
                'nino', 'niño', 'child' => TipoHuesped::NINO,
                default => TipoHuesped::ADULTO,
            };

            $registros[] = [
                'reserva_detalle_id' => $detalle->id,
                'nombre' => $huesped['nombre'],
                'identificacion' => is_string($huesped['identificacion'] ?? null) ? $huesped['identificacion'] : null,
                'tipo_huesped' => $tipo->value,
                'es_titular' => (bool) ($huesped['es_titular'] ?? false),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($registros !== []) {
            ReservaHuesped::insert($registros);
        }
    }

    private function aString(mixed $valor, string $default = ''): string
    {
        if (is_string($valor)) {
            return $valor;
        }

        if (is_numeric($valor)) {
            return (string) $valor;
        }

        return $default;
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
        $reserva->detalles()
            ->whereNotNull('parent_id')
            ->delete();

        $inicio = $principal->fecha_inicio;
        $fin = $principal->fecha_fin ?? $inicio;

        $todasLasSolicitudes = [];
        foreach ($habitaciones as $hab) {
            $todasLasSolicitudes[] = ['tipo' => TipoReserva::HABITACION, 'entidad_id' => $hab['habitacion_id']];
        }
        foreach ($servicios as $servicio) {
            $todasLasSolicitudes[] = ['tipo' => TipoReserva::SERVICIO, 'entidad_id' => $servicio['servicio_id']];
        }
        foreach ($espacios as $espacio) {
            $todasLasSolicitudes[] = ['tipo' => TipoReserva::RESTAURANTE, 'entidad_id' => $espacio['espacio_id']];
        }

        $recursos = $todasLasSolicitudes !== [] ? $this->resolverRecursosLote($todasLasSolicitudes) : [];

        $idx = 0;

        foreach ($habitaciones as $hab) {
            $recurso = $recursos[$idx++] ?? $this->resolverRecurso(TipoReserva::HABITACION, $hab['habitacion_id']);

            $this->crearDetalle($reserva, $recurso, [
                'parent_id' => $principal->id,
                'estado' => EstadoReservaDetalle::CONFIRMADO,
                'fecha_inicio' => $inicio,
                'fecha_fin' => $fin,
                'cantidad' => 1,
                'precio_unitario' => $hab['precio'],
                'subtotal' => round($hab['precio'] * max(1, (int) $principal->cantidad), 2),
            ]);
        }

        foreach ($servicios as $servicio) {
            $recurso = $recursos[$idx++] ?? $this->resolverRecurso(TipoReserva::SERVICIO, $servicio['servicio_id']);

            $this->crearDetalle($reserva, $recurso, [
                'parent_id' => $principal->id,
                'estado' => EstadoReservaDetalle::PENDIENTE,
                'fecha_inicio' => $inicio,
                'fecha_fin' => $fin,
                'cantidad' => $servicio['cantidad'],
                'precio_unitario' => $servicio['precio'],
                'subtotal' => round($servicio['precio'] * $servicio['cantidad'], 2),
            ]);
        }

        foreach ($espacios as $espacio) {
            $recurso = $recursos[$idx++] ?? $this->resolverRecurso(TipoReserva::RESTAURANTE, $espacio['espacio_id']);

            $horasVal = max(1, (int) $principal->cantidad);
            $mult = $this->tarifas->espacioEsPorHora($espacio['espacio_id']) ? $horasVal : 1;

            $this->crearDetalle($reserva, $recurso, [
                'parent_id' => $principal->id,
                'estado' => EstadoReservaDetalle::CONFIRMADO,
                'fecha_inicio' => $inicio,
                'fecha_fin' => $fin,
                'cantidad' => $espacio['cantidad'],
                'precio_unitario' => $espacio['precio'],
                'subtotal' => round($espacio['precio'] * $espacio['cantidad'] * $mult, 2),
            ]);
        }
    }

    /**
     * Crea detalles de habitaciones, servicios y espacios adicionales a partir de recursos resueltos.
     *
     * @param  array<int, RecursoReservable>  $recursos  Recursos resueltos en orden global
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
        $idx = 0;

        foreach ($habitaciones as $hab) {
            $recurso = $recursos[$idx++] ?? $this->resolverRecurso(TipoReserva::HABITACION, $hab['habitacion_id']);

            $this->crearDetalle($reserva, $recurso, [
                'parent_id' => $principal->id,
                'estado' => EstadoReservaDetalle::CONFIRMADO,
                'fecha_inicio' => $inicio,
                'fecha_fin' => $fin,
                'cantidad' => 1,
                'precio_unitario' => $hab['precio'],
                'subtotal' => round($hab['precio'] * $unidades, 2),
            ]);
        }

        foreach ($servicios as $servicio) {
            $recurso = $recursos[$idx++] ?? $this->resolverRecurso(TipoReserva::SERVICIO, $servicio['servicio_id']);

            $this->crearDetalle($reserva, $recurso, [
                'parent_id' => $principal->id,
                'estado' => EstadoReservaDetalle::PENDIENTE,
                'fecha_inicio' => $inicio,
                'fecha_fin' => $fin,
                'cantidad' => $servicio['cantidad'],
                'precio_unitario' => $servicio['precio'],
                'subtotal' => round($servicio['precio'] * $servicio['cantidad'], 2),
            ]);
        }

        foreach ($espacios as $espacio) {
            $recurso = $recursos[$idx++] ?? $this->resolverRecurso(TipoReserva::RESTAURANTE, $espacio['espacio_id']);

            $mult = $this->tarifas->espacioEsPorHora($espacio['espacio_id']) ? max(1, (int) $horasVal) : 1;

            $this->crearDetalle($reserva, $recurso, [
                'parent_id' => $principal->id,
                'estado' => EstadoReservaDetalle::CONFIRMADO,
                'fecha_inicio' => $inicio,
                'fecha_fin' => $fin,
                'cantidad' => $espacio['cantidad'],
                'precio_unitario' => $espacio['precio'],
                'subtotal' => round($espacio['precio'] * $espacio['cantidad'] * $mult, 2),
            ]);
        }
    }

    /** @param array<string, mixed> $datos */
    public function actualizarDetalle(ReservaDetalle $detalle, array $datos): ReservaDetalle
    {
        $detalle->update($datos);

        return $detalle->refresh();
    }

    public function detallePrincipalDe(Reserva $reserva): ReservaDetalle
    {
        /** @var ReservaDetalle|null $detalle */
        $detalle = $reserva->detalles()->whereNull('parent_id')->first();

        if ($detalle instanceof ReservaDetalle) {
            return $detalle;
        }

        $recurso = $this->resolverRecursoPrincipalDeReserva($reserva);
        [$inicio, $fin] = $this->periodoPrincipalDeReserva($reserva, $recurso);

        /** @var ReservaDetalle $nuevo */
        $nuevo = $reserva->detalles()->create([
            'reservable_id' => $recurso->id,
            'estado' => match ($reserva->estado) {
                EstadoReserva::PENDIENTE => EstadoReservaDetalle::PENDIENTE,
                EstadoReserva::CONFIRMADA => EstadoReservaDetalle::CONFIRMADO,
                EstadoReserva::PARCIALMENTE_CHECKED_IN => EstadoReservaDetalle::EN_USO,
                EstadoReserva::CHECKED_IN => EstadoReservaDetalle::EN_USO,
                EstadoReserva::PARCIALMENTE_CHECKED_OUT => EstadoReservaDetalle::EN_USO,
                EstadoReserva::CHECKED_OUT => EstadoReservaDetalle::COMPLETADO,
                EstadoReserva::CANCELADA => EstadoReservaDetalle::CANCELADO,
                EstadoReserva::NO_SHOW => EstadoReservaDetalle::CANCELADO,
            },
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin,
            'adultos' => (int) $reserva->adultos,
            'ninos' => (int) $reserva->ninos,
            'precio_unitario' => (float) ($reserva->subtotal ?? $reserva->total ?? 0),
            'descuento' => (float) $reserva->descuento,
            'subtotal' => (float) ($reserva->subtotal ?? $reserva->total ?? 0),
            'notas' => $reserva->notas,
        ]);

        return $nuevo;
    }

    /** @param array<string, mixed> $datos */
    public function crearHuesped(ReservaDetalle $detalle, array $datos): ReservaHuesped
    {
        /** @var ReservaHuesped $huesped */
        $huesped = $detalle->huespedes()->create($datos);

        return $huesped;
    }

    /** @param array<string, mixed> $datos */
    public function actualizarHuesped(ReservaHuesped $huesped, array $datos): ReservaHuesped
    {
        $huesped->update($datos);

        /** @var ReservaHuesped $actualizado */
        $actualizado = $huesped->fresh() ?? $huesped;

        return $actualizado;
    }

    public function eliminarHuesped(ReservaHuesped $huesped): void
    {
        $huesped->delete();
    }

    /** @param array<string, mixed> $datos */
    public function crearEstancia(array $datos): Estancia
    {
        return Estancia::query()->create($datos);
    }

    public function estanciaActivaDeReserva(Reserva $reserva): Estancia
    {
        /** @var Estancia $estancia */
        $estancia = Estancia::query()
            ->with('cuenta')
            ->whereBelongsTo($reserva)
            ->lockForUpdate()
            ->firstOrFail();

        return $estancia;
    }

    /** @param array<string, mixed> $datos */
    public function actualizarEstancia(Estancia $estancia, array $datos): Estancia
    {
        $estancia->update($datos);

        return $estancia->refresh();
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

    /** @return array{Habitacion, TipoRecursoReservable, ControlDisponibilidad, string, int|null} */
    private function datosHabitacion(int $id): array
    {
        $habitacion = Habitacion::query()->lockForUpdate()->findOrFail($id);

        return [$habitacion, TipoRecursoReservable::HABITACION, ControlDisponibilidad::FECHAS, (string) $habitacion->nombre, null];
    }

    /** @return array{Espacio, TipoRecursoReservable, ControlDisponibilidad, string, int|null} */
    private function datosEspacio(int $id): array
    {
        $espacio = Espacio::query()->lockForUpdate()->findOrFail($id);

        return [$espacio, TipoRecursoReservable::ESPACIO, ControlDisponibilidad::HORARIO, (string) $espacio->nombre, (int) $espacio->capacidad_personas];
    }

    /** @return array{Servicio, TipoRecursoReservable, ControlDisponibilidad, string, null} */
    private function datosServicio(int $id): array
    {
        $servicio = Servicio::query()->lockForUpdate()->findOrFail($id);

        return [$servicio, TipoRecursoReservable::SERVICIO, ControlDisponibilidad::SIN_BLOQUEO, (string) $servicio->nombre, null];
    }

    private function resolverRecursoPrincipalDeReserva(Reserva $reserva): RecursoReservable
    {
        return match ($reserva->tipo_reserva) {
            TipoReserva::HABITACION => $this->resolverRecursoDesdeId($reserva, TipoReserva::HABITACION, $reserva->habitacion_id, 'habitación'),
            TipoReserva::RESTAURANTE => $this->resolverRecursoDesdeId($reserva, TipoReserva::RESTAURANTE, $reserva->espacio_id, 'mesa/espacio'),
            TipoReserva::SERVICIO => $this->resolverRecursoDesdeId($reserva, TipoReserva::SERVICIO, $reserva->servicio_id, 'servicio'),
            TipoReserva::PAQUETE => is_numeric($reserva->habitacion_id)
                ? $this->resolverRecursoDesdeId($reserva, TipoReserva::HABITACION, $reserva->habitacion_id, 'habitación')
                : (is_numeric($reserva->espacio_id)
                    ? $this->resolverRecursoDesdeId($reserva, TipoReserva::RESTAURANTE, $reserva->espacio_id, 'mesa/espacio')
                    : $this->resolverRecursoDesdeId($reserva, TipoReserva::SERVICIO, $reserva->servicio_id, 'servicio')),
        };
    }

    private function resolverRecursoDesdeId(Reserva $reserva, TipoReserva $tipo, mixed $id, string $nombreCampo): RecursoReservable
    {
        if (! is_numeric($id) || (int) $id <= 0) {
            throw new InvalidArgumentException("La reserva {$reserva->codigo_reserva} no tiene {$nombreCampo} principal asociado.");
        }

        return $this->resolverRecurso($tipo, (int) $id);
    }

    /** @return array{DateTimeImmutable, DateTimeImmutable} */
    private function periodoPrincipalDeReserva(Reserva $reserva, RecursoReservable $recurso): array
    {
        $fechaInicio = $reserva->fecha_check_in?->format('Y-m-d');
        if ($fechaInicio === null) {
            throw new InvalidArgumentException("La reserva {$reserva->codigo_reserva} no tiene fecha de inicio.");
        }

        $hora = trim((string) ($reserva->hora_reserva ?? ''));
        $inicio = new DateTimeImmutable($fechaInicio.' '.($hora !== '' ? $hora : '00:00'));

        if ($reserva->fecha_check_out !== null) {
            $fechaFin = $reserva->fecha_check_out->format('Y-m-d');
            $fin = new DateTimeImmutable($fechaFin.' '.($hora !== '' ? $hora : '23:59:59'));
        } else {
            $duracion = $recurso->duracion_minutos !== null && $recurso->duracion_minutos > 0
                ? $recurso->duracion_minutos
                : 60;
            $fin = $inicio->modify("+{$duracion} minutes");
        }

        if ($fin <= $inicio) {
            $fin = $inicio->modify('+1 hour');
        }

        return [$inicio, $fin];
    }

    /**
     * Calcula la duración actual en horas de la reserva basándose en el detalle principal.
     *
     * Utiliza detallesPrincipalesDe() que NO crea registros al no encontrar detalle principal,
     * a diferencia de detallePrincipalDe() que sí crea uno.
     *
     * @return int|null Horas de duración (mínimo 1) o null si no hay detalle o fechas definidas
     */
    public function duracionHorasActual(Reserva $reserva): ?int
    {
        $detalles = $this->detallesPrincipalesDe($reserva);
        $principal = $detalles->first();

        if ($principal === null) {
            return null;
        }

        $fechaFin = $principal->fecha_fin;
        $fechaInicio = $principal->fecha_inicio;

        if ($fechaFin === null || $fechaInicio === null) { // @phpstan-ignore identical.alwaysFalse
            return null;
        }

        $horas = (int) ceil(($fechaFin->getTimestamp() - $fechaInicio->getTimestamp()) / 3600);

        return $horas > 0 ? $horas : 1;
    }
}
