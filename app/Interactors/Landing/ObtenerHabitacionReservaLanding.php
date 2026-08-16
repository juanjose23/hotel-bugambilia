<?php

declare(strict_types=1);

namespace App\Interactors\Landing;

use App\Repository\Queries\Reservas\ObtenerDiasAgotadosHabitacionQuery;
use App\Repository\Queries\Reservas\ObtenerOpcionesReservaPublicaQuery;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

final readonly class ObtenerHabitacionReservaLanding
{
    public function __construct(
        private ObtenerHabitacionDetalleLanding $habitacionDetalle,
        private ObtenerOpcionesReservaPublicaQuery $opcionesReserva,
        private ObtenerDiasAgotadosHabitacionQuery $diasAgotados,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function ejecutar(string $slug): array
    {
        $data = $this->habitacionDetalle->ejecutar($slug);
        $habitacionId = $this->enteroOpcional($data['room']['id'] ?? null);

        $disponibilidad = $habitacionId !== null
            ? $this->diasAgotados->porHabitacion(
                habitacionId: $habitacionId,
                inicio: now()->startOfDay(),
                fin: now()->addMonths(18)->startOfDay(),
            )
            : ['dias_agotados' => [], 'ocupacion_por_dia' => [], 'total_habitaciones' => 0];

        return [
            'room' => $data['room'],
            'opcionesReserva' => $this->opcionesReserva->obtener(),
            'diasAgotadosHabitacion' => $disponibilidad['dias_agotados'],
            'ocupacionHabitacionPorDia' => $disponibilidad['ocupacion_por_dia'],
            'totalHabitacionesCategoria' => $disponibilidad['total_habitaciones'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function recomendarDisponibilidad(
        string $slug,
        CarbonInterface $checkIn,
        CarbonInterface $checkOut,
        int $adultos,
        int $ninos,
    ): array {
        $data = $this->habitacionDetalle->ejecutar($slug);
        $categoriaId = $this->enteroOpcional($data['room']['categoria_id'] ?? null);
        $ubicacionId = $this->enteroOpcional($data['room']['ubicacion_id'] ?? null);

        if ($categoriaId === null) {
            return [
                'disponible' => false,
                'noches' => 0,
                'total_habitaciones' => 0,
                'disponibilidad_minima' => 0,
                'dias_sin_disponibilidad' => [],
                'recomendaciones' => [],
            ];
        }

        return $this->diasAgotados->recomendarPorCategoria(
            categoriaId: $categoriaId,
            checkIn: $checkIn,
            checkOut: $checkOut,
            adultos: $adultos,
            ninos: $ninos,
            ubicacionId: $ubicacionId,
        );
    }

    /**
     * @return array{
     *     total_habitaciones: int,
     *     dias_agotados: array<int, string>,
     *     ocupacion_por_dia: array<string, array{ocupadas: int, total: int, disponibles: int, agotado: bool}>,
     *     calendario: array<int, array<string, mixed>>
     * }
     */
    public function calendarioDisponibilidad(string $slug, int $meses): array
    {
        $data = $this->habitacionDetalle->ejecutar($slug);
        $categoriaId = $this->enteroOpcional($data['room']['categoria_id'] ?? null);
        $ubicacionId = $this->enteroOpcional($data['room']['ubicacion_id'] ?? null);

        if ($categoriaId === null) {
            return [
                'total_habitaciones' => 0,
                'dias_agotados' => [],
                'ocupacion_por_dia' => [],
                'calendario' => [],
            ];
        }

        $inicio = CarbonImmutable::now()->startOfDay();
        $fin = $inicio->addMonths($meses)->startOfDay();
        $disponibilidad = $this->diasAgotados->porCategoria(
            categoriaId: $categoriaId,
            inicio: $inicio,
            fin: $fin,
            ubicacionId: $ubicacionId,
        );

        return [
            'total_habitaciones' => $disponibilidad['total_habitaciones'],
            'dias_agotados' => $disponibilidad['dias_agotados'],
            'ocupacion_por_dia' => $disponibilidad['ocupacion_por_dia'],
            'calendario' => $this->construirCalendario($inicio, $fin, $disponibilidad),
        ];
    }

    private function enteroOpcional(mixed $valor): ?int
    {
        return is_numeric($valor) ? (int) $valor : null;
    }

    /**
     * @param  array{
     *     total_habitaciones: int,
     *     dias_agotados: array<int, string>,
     *     ocupacion_por_dia: array<string, array{ocupadas: int, total: int, disponibles: int, agotado: bool}>
     * }  $disponibilidad
     * @return array<int, array<string, mixed>>
     */
    private function construirCalendario(
        CarbonImmutable $inicio,
        CarbonImmutable $fin,
        array $disponibilidad,
    ): array {
        CarbonImmutable::setLocale('es');

        $ocupacion = $disponibilidad['ocupacion_por_dia'];
        $totalHabitaciones = $disponibilidad['total_habitaciones'];
        $calendarioMeses = [];

        $cursor = $inicio->copy();
        while ($cursor->lessThan($fin)) {
            $year = $cursor->year;
            $month = $cursor->month;
            $dias = [];

            for ($d = 1; $d <= $cursor->daysInMonth; $d++) {
                $fechaCarbon = $cursor->setDate($year, $month, $d);
                if ($fechaCarbon->lessThan($inicio) || $fechaCarbon->greaterThanOrEqualTo($fin)) {
                    continue;
                }

                $fechaStr = $fechaCarbon->format('Y-m-d');
                $datosOcup = $ocupacion[$fechaStr] ?? [
                    'ocupadas' => 0,
                    'total' => $totalHabitaciones,
                    'disponibles' => $totalHabitaciones,
                    'agotado' => false,
                ];

                $dias[] = [
                    'fecha' => $fechaStr,
                    'dia' => $d,
                    'dia_semana' => $fechaCarbon->dayOfWeekIso,
                    'disponible' => ! $datosOcup['agotado'],
                    'habitaciones_disponibles' => $datosOcup['disponibles'],
                    'habitaciones_ocupadas' => $datosOcup['ocupadas'],
                    'habitaciones_totales' => $datosOcup['total'],
                ];
            }

            $calendarioMeses[] = [
                'mes' => ucfirst($cursor->isoFormat('MMMM YYYY')),
                'year' => $year,
                'month' => $month,
                'dias' => $dias,
            ];

            $cursor = $cursor->startOfMonth()->addMonth();
        }

        return $calendarioMeses;
    }
}
