<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Reservas;

use App\Enums\Reservas\ControlDisponibilidad;
use App\Enums\Reservas\EstadoRecursoReservable;
use App\Enums\Reservas\TipoRecursoReservable;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Servicios\Servicio;

final readonly class RecursoReservableRepositorio
{
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

    /** @param array<int, int> $ids */
    public function bloquearRecursosReservables(array $ids): void
    {
        RecursoReservable::query()
            ->whereIn('id', $ids)
            ->lockForUpdate()
            ->get();
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
     * 1. Los paquetes se resuelven individualmente (su entidad puede ser habitación, espacio o servicio)
     * 2. Agrupa el resto de entidades por tipo y ejecuta una query por tipo (Habitación, Espacio, Servicio)
     * 3. Para entidades existentes, obtiene el recurso asociado
     * 4. Para entidades nuevas, crea el recurso dentro de la misma transacción
     * 5. Utiliza bloqueo SELECT FOR UPDATE para prevenir condiciones de carrera
     *
     * @param  array<int, array{tipo: TipoReserva, entidad_id: int}>  $solicitudes
     * @return array<int, RecursoReservable> Indexadas por la clave original del array de entrada
     */
    public function resolverRecursosLote(array $solicitudes): array
    {
        if ($solicitudes === []) {
            return [];
        }

        // Los paquetes no tienen una entidad fija (puede ser habitación, espacio o servicio),
        // por lo que no pueden agruparse en una query por tipo: se resuelven individualmente.
        $resultadosPaquete = [];
        $restantes = [];
        foreach ($solicitudes as $idx => $solicitud) {
            if ($solicitud['tipo'] === TipoReserva::PAQUETE) {
                $resultadosPaquete[$idx] = $this->resolverRecurso(TipoReserva::PAQUETE, (int) $solicitud['entidad_id']);
            } else {
                $restantes[$idx] = $solicitud;
            }
        }

        if ($restantes === []) {
            return $resultadosPaquete;
        }

        $solicitudes = $restantes;

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

        return $resultados + $resultadosPaquete;
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
}
