<?php

declare(strict_types=1);

namespace App\Interactors\Habitaciones;

use App\Presenters\Habitaciones\HabitacionDetallePresenter;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Queries\Habitaciones\ObtenerHabitacionDetalleWebQuery;
use App\Repository\Queries\Promociones\ObtenerBeneficiosClienteElegiblesQuery;
use App\Repository\Queries\Reservas\ObtenerDiasAgotadosHabitacionQuery;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;

final readonly class ObtenerHabitacionDetalle
{
    public function __construct(
        private HabitacionDetallePresenter $presenter,
        private ObtenerBeneficiosClienteElegiblesQuery $obtenerBeneficios,
        private ObtenerDiasAgotadosHabitacionQuery $diasAgotadosQuery,
        private ObtenerHabitacionDetalleWebQuery $habitacionDetalleQuery,
    ) {}

    /**
     * @return array{
     *     room: array<string, mixed>,
     *     similarRooms: array<int, array<string, mixed>>,
     *     serviciosDisponibles: array<int, array{id: int, nombre: string, precio: float, moneda: string, descripcion: string}>,
     *     beneficiosCliente: array<int, array{id: int, nombre: string, tipo: string, valor: float, es_porcentaje: bool, descripcion: string}>,
     *     diasAgotados: array<int, string>
     * }
     */
    public function ejecutar(string $slug): array
    {
        $habitacion = $this->habitacionDetalleQuery->resolverHabitacion($slug);

        if (! $habitacion instanceof Habitacion) {
            abort(404, 'Habitación no encontrada.');
        }

        $cliente = Auth::user()?->persona?->cliente;
        $beneficios = [];

        if ($cliente !== null) {
            $beneficios = $this->obtenerBeneficios
                ->paraCliente($cliente, ['categoria_habitacion_id' => $habitacion->categoria_id])
                ->map(fn ($b): array => [
                    'id' => (int) $b->id,
                    'nombre' => (string) ($b->promocion->nombre ?? $b->tipo->getLabel()),
                    'tipo' => (string) $b->tipo->value,
                    'valor' => (float) $b->valor,
                    'es_porcentaje' => (bool) $b->es_porcentaje,
                    'descripcion' => (string) ($b->promocion->descripcion ?? ''),
                ])
                ->values()
                ->all();
        }

        $serviciosDisponibles = $this->habitacionDetalleQuery
            ->obtenerServiciosDisponiblesWeb()
            ->map(function (Servicio $s): array {
                $p = $s->precios->first();

                return [
                    'id' => (int) $s->id,
                    'nombre' => (string) $s->nombre,
                    'precio' => $p !== null ? (float) $p->precio : 0.0,
                    'moneda' => (string) ($p !== null ? ($p->moneda->simbolo ?? '$') : '$'),
                    'descripcion' => (string) ($s->descripcion ?? ''),
                ];
            })
            ->values()
            ->all();

        $inicio = CarbonImmutable::now()->startOfDay();
        $fin = $inicio->addMonths(12)->startOfDay();
        $disponibilidad = $this->diasAgotadosQuery->porCategoria((int) $habitacion->categoria_id, $inicio, $fin, ubicacionId: $habitacion->ubicacion_id);

        return [
            'room' => $this->presenter->detalle($habitacion),
            'similarRooms' => $this->presenter->similares($habitacion),
            'serviciosDisponibles' => $serviciosDisponibles,
            'beneficiosCliente' => $beneficios,
            'diasAgotados' => $disponibilidad['dias_agotados'],
        ];
    }
}
