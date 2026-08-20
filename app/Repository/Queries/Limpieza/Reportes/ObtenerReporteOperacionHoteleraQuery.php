<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Reportes;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Limpieza\SustitucionStock;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

final readonly class ObtenerReporteOperacionHoteleraQuery
{
    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function ejecutar(array $params = []): array
    {
        $fechaInicio = $this->fecha($params['fecha_desde'] ?? $params['fecha_inicio'] ?? null, now()->startOfMonth()->toDateString());
        $fechaFin = $this->fecha($params['fecha_hasta'] ?? $params['fecha_fin'] ?? null, now()->toDateString());

        /** @var Collection<int, LimpiezaEjecucion> $ejecuciones */
        $ejecuciones = LimpiezaEjecucion::query()
            ->with(['limpiable', 'colaborador.persona', 'turno'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->orderBy('fecha')
            ->orderBy('turno_id')
            ->get();

        $finalizadas = $ejecuciones->filter(fn (LimpiezaEjecucion $ejecucion): bool => $ejecucion->estado->estaFinalizada());

        $tiempos = $finalizadas
            ->map(fn (LimpiezaEjecucion $ejecucion): ?int => $this->minutosLimpieza($ejecucion))
            ->filter(fn (?int $minutos): bool => $minutos !== null && $minutos > 0)
            ->values();

        return [
            'filtros' => [
                'fecha_desde' => $fechaInicio,
                'fecha_hasta' => $fechaFin,
            ],
            'resumen' => [
                'ejecuciones' => $ejecuciones->count(),
                'finalizadas' => $finalizadas->count(),
                'pendientes' => $ejecuciones->where('estado', EstadoLimpieza::Pendiente)->count(),
                'en_progreso' => $ejecuciones->where('estado', EstadoLimpieza::EnProgreso)->count(),
                'bloqueadas' => $this->habitacionesBloqueadas()->count(),
                'tiempo_promedio_minutos' => $tiempos->isEmpty() ? 0 : (int) round($this->numero($tiempos->avg())),
            ],
            'tiempos_por_habitacion' => $this->tiemposPorHabitacion($finalizadas),
            'pendientes_bloqueadas' => $this->habitacionesPendientesYBloqueadas($ejecuciones),
            'amenities_por_habitacion' => $this->amenitiesPorHabitacion($ejecuciones, $fechaInicio, $fechaFin),
            'productividad' => $this->productividadPorColaboradorTurno($ejecuciones),
        ];
    }

    private function fecha(mixed $valor, string $porDefecto): string
    {
        if (! is_string($valor) || trim($valor) === '') {
            return $porDefecto;
        }

        return Carbon::parse($valor)->toDateString();
    }

    private function minutosLimpieza(LimpiezaEjecucion $ejecucion): ?int
    {
        if (! $ejecucion->hora_inicio || ! $ejecucion->hora_fin) {
            return null;
        }

        $inicio = Carbon::parse($ejecucion->fecha->toDateString().' '.$ejecucion->hora_inicio);
        $fin = Carbon::parse($ejecucion->fecha->toDateString().' '.$ejecucion->hora_fin);

        return $fin->greaterThan($inicio) ? (int) $inicio->diffInMinutes($fin) : null;
    }

    /**
     * @param  Collection<int, LimpiezaEjecucion>  $ejecuciones
     * @return array<int, array<string, mixed>>
     */
    private function tiemposPorHabitacion(Collection $ejecuciones): array
    {
        return $ejecuciones
            ->map(function (LimpiezaEjecucion $ejecucion): ?array {
                $minutos = $this->minutosLimpieza($ejecucion);

                if ($minutos === null) {
                    return null;
                }

                return [
                    'fecha' => $ejecucion->fecha->format('d/m/Y'),
                    'habitacion' => $this->nombreLimpiable($ejecucion),
                    'turno' => $this->texto(data_get($ejecucion->turno, 'nombre')) ?: 'Sin turno',
                    'colaborador' => $this->nombreColaborador($ejecucion),
                    'minutos' => $minutos,
                    'estado' => $ejecucion->estado->getLabel(),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, LimpiezaEjecucion>  $ejecuciones
     * @return array<int, array<string, mixed>>
     */
    private function habitacionesPendientesYBloqueadas(Collection $ejecuciones): array
    {
        $pendientes = $ejecuciones
            ->filter(fn (LimpiezaEjecucion $ejecucion): bool => in_array($ejecucion->estado, [EstadoLimpieza::Pendiente, EstadoLimpieza::EnProgreso], strict: true))
            ->map(fn (LimpiezaEjecucion $ejecucion): array => [
                'habitacion' => $this->nombreLimpiable($ejecucion),
                'estado' => $ejecucion->estado->getLabel(),
                'motivo' => $ejecucion->estado === EstadoLimpieza::Pendiente ? 'Limpieza pendiente' : 'Limpieza en proceso',
                'fecha' => $ejecucion->fecha->format('d/m/Y'),
            ]);

        $bloqueadas = $this->habitacionesBloqueadas()
            ->map(fn (Habitacion $habitacion): array => [
                'habitacion' => $habitacion->nombre ?? $habitacion->codigo ?? 'Habitación #'.$habitacion->id,
                'estado' => $habitacion->estado->getLabel(),
                'motivo' => 'Habitación bloqueada para operación',
                'fecha' => '-',
            ]);

        return $pendientes->merge($bloqueadas)->values()->all();
    }

    /** @return Collection<int, Habitacion> */
    private function habitacionesBloqueadas(): Collection
    {
        return Habitacion::query()
            ->whereIn('estado', [
                EstadoEspacio::Mantenimiento->value,
                EstadoEspacio::Limpieza->value,
                EstadoEspacio::Sucio->value,
                EstadoEspacio::Inactivo->value,
            ])
            ->orderBy('numero')
            ->get();
    }

    /**
     * @param  Collection<int, LimpiezaEjecucion>  $ejecuciones
     * @return array<int, array<string, mixed>>
     */
    private function amenitiesPorHabitacion(Collection $ejecuciones, string $fechaInicio, string $fechaFin): array
    {
        $productos = Producto::query()->pluck('nombre', 'id');
        $amenities = collect();

        foreach ($ejecuciones as $ejecucion) {
            foreach ($this->normalizarConsumos($ejecucion->consumos) as $consumo) {
                $amenities->push([
                    'habitacion' => $this->nombreLimpiable($ejecucion),
                    'producto' => $consumo['producto'],
                    'cantidad' => $consumo['cantidad'],
                ]);
            }
        }

        SustitucionStock::query()
            ->with(['ejecucion.limpiable', 'productoOriginal'])
            ->whereHas('ejecucion', fn ($query) => $query->whereBetween('fecha', [$fechaInicio, $fechaFin]))
            ->get()
            ->each(function (SustitucionStock $sustitucion) use ($amenities, $productos): void {
                if (! $sustitucion->ejecucion) {
                    return;
                }

                $producto = $sustitucion->productoOriginal->nombre
                    ?? $productos->get($sustitucion->producto_id)
                    ?? 'Producto #'.$sustitucion->producto_id;

                $amenities->push([
                    'habitacion' => $this->nombreLimpiable($sustitucion->ejecucion),
                    'producto' => $producto,
                    'cantidad' => $this->numero($sustitucion->cantidad),
                ]);
            });

        return $amenities
            ->groupBy(fn (array $item): string => $item['habitacion'].'|'.$item['producto'])
            ->map(fn (Collection $items): array => [
                'habitacion' => (string) $items->first()['habitacion'],
                'producto' => (string) $items->first()['producto'],
                'cantidad' => $this->numero($items->sum('cantidad')),
            ])
            ->sortBy(['habitacion', 'producto'])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{producto: string, cantidad: float}>
     */
    private function normalizarConsumos(mixed $consumos): array
    {
        if (! is_array($consumos)) {
            return [];
        }

        return collect($consumos)
            ->map(function (mixed $item, mixed $key): ?array {
                if (is_array($item)) {
                    $producto = $item['producto'] ?? $item['nombre'] ?? $item['producto_nombre'] ?? null;
                    if (! is_string($producto) && is_numeric($item['producto_id'] ?? null)) {
                        $producto = 'Producto #'.$item['producto_id'];
                    }
                    $producto ??= $key;
                    $cantidad = $item['cantidad'] ?? $item['qty'] ?? 0;
                } else {
                    $producto = $key;
                    $cantidad = $item;
                }

                if (! is_string($producto) || ! is_numeric($cantidad)) {
                    return null;
                }

                return ['producto' => $producto, 'cantidad' => (float) $cantidad];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, LimpiezaEjecucion>  $ejecuciones
     * @return array<int, array<string, mixed>>
     */
    private function productividadPorColaboradorTurno(Collection $ejecuciones): array
    {
        return $ejecuciones
            ->groupBy(fn (LimpiezaEjecucion $ejecucion): string => ($ejecucion->colaborador_id ?? 0).'|'.($ejecucion->turno_id ?? 0))
            ->map(function (Collection $items): array {
                /** @var LimpiezaEjecucion $primera */
                $primera = $items->first();
                $finalizadas = $items->filter(fn (LimpiezaEjecucion $ejecucion): bool => $ejecucion->estado->estaFinalizada());
                $minutos = $finalizadas
                    ->map(fn (LimpiezaEjecucion $ejecucion): ?int => $this->minutosLimpieza($ejecucion))
                    ->filter(fn (?int $valor): bool => $valor !== null);

                return [
                    'colaborador' => $this->nombreColaborador($primera),
                    'turno' => $this->texto(data_get($primera->turno, 'nombre')) ?: 'Sin turno',
                    'asignadas' => $items->count(),
                    'finalizadas' => $finalizadas->count(),
                    'promedio_minutos' => $minutos->isEmpty() ? 0 : (int) round($this->numero($minutos->avg())),
                ];
            })
            ->sortByDesc('finalizadas')
            ->values()
            ->all();
    }

    private function nombreLimpiable(LimpiezaEjecucion $ejecucion): string
    {
        $limpiable = $ejecucion->limpiable;

        return $this->texto(data_get($limpiable, 'nombre'))
            ?: ($this->texto(data_get($limpiable, 'codigo')) ?: 'Área #'.$ejecucion->limpiable_id);
    }

    private function nombreColaborador(LimpiezaEjecucion $ejecucion): string
    {
        return $this->texto(data_get($ejecucion->colaborador, 'persona.nombre_completo'))
            ?: ($this->texto(data_get($ejecucion->colaborador, 'codigo')) ?: 'Sin asignar');
    }

    private function numero(mixed $valor): float
    {
        return is_numeric($valor) ? (float) $valor : 0.0;
    }

    private function texto(mixed $valor): string
    {
        return is_scalar($valor) || $valor === null ? trim((string) $valor) : '';
    }
}
