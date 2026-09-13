<?php

declare(strict_types=1);

namespace App\Interactors\Landing;

use App\BusinessLogic\Restaurante\Mesas\ResolverUnionMesasAuto;
use App\Enums\Reservas\ControlDisponibilidad;
use App\Enums\Reservas\TipoReserva;
use App\Presenters\Landing\MesaDisponiblePresenter;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use App\Repository\Queries\Reservas\DisponibilidadRecursoQuery;
use App\Repository\Queries\Restaurante\Mesas\ObtenerMesasActivasQuery;
use Carbon\Carbon;
use DateTimeImmutable;

final readonly class ObtenerMesasDisponiblesLanding
{
    public function __construct(
        private DisponibilidadRecursoQuery $disponibilidadQuery,
        private ResolverUnionMesasAuto $resolverUnionMesas,
        private ReservaRepositorioInterface $reservasRepositorio,
        private ObtenerMesasActivasQuery $obtenerMesasActivas,
        private MesaDisponiblePresenter $mesaPresenter,
    ) {}

    /**
     * @return array{
     *     fecha: string,
     *     hora: string,
     *     duracion_horas: int,
     *     comensales: int,
     *     mesa_asignada: ?array{id: int, nombre: string, capacidad: int, ubicacion: string, zona: string},
     *     requiere_union: bool,
     *     zona_asignada: string,
     *     mesas_disponibles: array<int, array{id: int, nombre: string, capacidad: int, ubicacion: string}>,
     *     mesas_sugeridas_union: array<int, array{id: int, nombre: string, capacidad: int, ubicacion: string, zona: string}>,
     *     capacidad_total_disponible: int,
     *     horarios_disponibles: array<int, array{hora: string, turno: string, disponible: bool}>
     * }
     */
    public function ejecutar(
        string $fecha,
        string $hora = '13:00',
        int $duracionHoras = 1,
        int $comensales = 2,
    ): array {
        $fechaCarbon = Carbon::parse($fecha)->startOfDay();
        $horasCatalogo = [
            // Turno Mañanas
            ['hora' => '07:30', 'turno' => 'Desayuno'],
            ['hora' => '08:30', 'turno' => 'Desayuno'],
            ['hora' => '09:30', 'turno' => 'Desayuno'],
            // Turno Mediodía
            ['hora' => '12:00', 'turno' => 'Almuerzo'],
            ['hora' => '13:00', 'turno' => 'Almuerzo'],
            ['hora' => '14:00', 'turno' => 'Almuerzo'],
            ['hora' => '15:00', 'turno' => 'Almuerzo'],
            // Turno Noches
            ['hora' => '18:00', 'turno' => 'Cena'],
            ['hora' => '19:00', 'turno' => 'Cena'],
            ['hora' => '20:00', 'turno' => 'Cena'],
            ['hora' => '21:00', 'turno' => 'Cena'],
        ];

        $mesas = $this->obtenerMesasActivas->ejecutar();

        $inicioSolicitado = new DateTimeImmutable($fechaCarbon->format('Y-m-d').' '.$hora);
        $finSolicitado = $inicioSolicitado->modify("+{$duracionHoras} hours");

        $mesasLibres = [];
        $mesasCollectionLibres = collect();
        $capacidadTotal = 0;

        foreach ($mesas as $mesa) {
            $recurso = $this->reservasRepositorio->resolverRecurso(TipoReserva::RESTAURANTE, (int) $mesa->id);

            $hayConflicto = false;
            if ($recurso->control_disponibilidad !== ControlDisponibilidad::SIN_BLOQUEO) {
                $hayConflicto = $this->disponibilidadQuery->existeConflicto(
                    recursoId: (int) $recurso->id,
                    inicio: $inicioSolicitado,
                    fin: $finSolicitado,
                );
            }

            if (! $hayConflicto) {
                $mesasLibres[] = $this->mesaPresenter->mesaLibre($mesa);
                $mesasCollectionLibres->push($mesa);
                $capacidadTotal += $this->mesaPresenter->capacidad($mesa);
            }
        }

        // Determinar asignación óptima de mesa (individual o unión de mesas)
        $asignacion = $this->resolverUnionMesas->seleccionarMejorAsignacion($comensales, $mesasCollectionLibres);
        $mesaAsignada = null;
        $mesasSugeridasUnion = [];

        if ($asignacion['mesa_principal'] instanceof Espacio) {
            $mesaPrincipalModel = $asignacion['mesa_principal'];
            $mesaAsignada = $this->mesaPresenter->mesaConZona($mesaPrincipalModel, $asignacion['zona']);

            if ($asignacion['requiere_union'] && ! empty($asignacion['mesas_unidas'])) {
                foreach ($asignacion['mesas_unidas'] as $mUnida) {
                    $mesasSugeridasUnion[] = $this->mesaPresenter->mesaConZona($mUnida, $asignacion['zona']);
                }
            }
        }

        // Evaluar disponibilidad de horarios generales para la fecha
        $horariosDisponibles = [];
        foreach ($horasCatalogo as $slot) {
            $slotInicio = new DateTimeImmutable($fechaCarbon->format('Y-m-d').' '.$slot['hora']);
            $slotFin = $slotInicio->modify("+{$duracionHoras} hours");

            $alMenosUnaLibre = false;
            foreach ($mesas as $m) {
                $rec = $this->reservasRepositorio->resolverRecurso(TipoReserva::RESTAURANTE, (int) $m->id);
                if (! $this->disponibilidadQuery->existeConflicto((int) $rec->id, $slotInicio, $slotFin)) {
                    $alMenosUnaLibre = true;
                    break;
                }
            }

            $horariosDisponibles[] = [
                'hora' => $slot['hora'],
                'turno' => $slot['turno'],
                'disponible' => $alMenosUnaLibre,
            ];
        }

        return [
            'fecha' => $fechaCarbon->format('Y-m-d'),
            'hora' => $hora,
            'duracion_horas' => $duracionHoras,
            'comensales' => $comensales,
            'mesa_asignada' => $mesaAsignada,
            'requiere_union' => $asignacion['requiere_union'],
            'zona_asignada' => $asignacion['zona'],
            'mesas_disponibles' => $mesasLibres,
            'mesas_sugeridas_union' => $mesasSugeridasUnion,
            'capacidad_total_disponible' => $capacidadTotal,
            'horarios_disponibles' => $horariosDisponibles,
        ];
    }
}
