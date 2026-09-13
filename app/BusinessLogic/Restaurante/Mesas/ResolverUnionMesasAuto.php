<?php

declare(strict_types=1);

namespace App\BusinessLogic\Restaurante\Mesas;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Repository\Models\Espacios\Espacio;
use Illuminate\Support\Collection;

final class ResolverUnionMesasAuto
{
    /**
     * Calcula y recomienda las mesas secundarias libres necesarias para completar la capacidad solicitada.
     *
     * @param  Espacio  $mesaPrincipal  Mesa seleccionada como principal
     * @param  int  $comensalesTotales  Cantidad de personas requerida
     * @param  Collection<int, Espacio>  $mesasDisponibles  Listado de mesas libres en el mismo sector/restaurante
     * @return int[] IDs de las mesas secundarias a unir
     */
    public function resolver(
        Espacio $mesaPrincipal,
        int $comensalesTotales,
        Collection $mesasDisponibles
    ): array {
        $capacidadActual = (int) $mesaPrincipal->capacidad_personas;

        if ($capacidadActual >= $comensalesTotales) {
            return [];
        }

        $deficit = $comensalesTotales - $capacidadActual;
        $secundariasParaUnir = [];
        $capacidadAcumulada = 0;

        $metaPrincipal = is_array($mesaPrincipal->meta_datos) ? $mesaPrincipal->meta_datos : [];
        $zonaPrincipal = $metaPrincipal['zona_restaurante'] ?? null;
        $tipoPrincipal = $metaPrincipal['tipo_mesa'] ?? '';

        // Filtrar mesas en la misma área/padre que estén completamente disponibles
        $candidatas = $mesasDisponibles
            ->filter(function (Espacio $m) use ($mesaPrincipal, $zonaPrincipal, $tipoPrincipal): bool {
                if ($m->id === $mesaPrincipal->id || $m->estado !== EstadoEspacio::Disponible) {
                    return false;
                }

                $meta = is_array($m->meta_datos) ? $m->meta_datos : [];
                $tipoMesa = (string) ($meta['tipo_mesa'] ?? '');
                $zonaMesa = (string) ($meta['zona_restaurante'] ?? '');

                // Jamás unir asientos de barra con mesas de comedor
                if ($tipoPrincipal === 'barra' || $tipoMesa === 'barra') {
                    return false;
                }

                // Jamás unir mesas de distintas zonas de restaurante
                if ($zonaPrincipal !== null && $zonaMesa !== '' && $zonaMesa !== (string) $zonaPrincipal) {
                    return false;
                }

                return true;
            })
            ->sortByDesc(fn (Espacio $m): int => (int) $m->capacidad_personas);

        foreach ($candidatas as $candidata) {
            $capCandidata = (int) $candidata->capacidad_personas;
            $secundariasParaUnir[] = (int) $candidata->id;
            $capacidadAcumulada += $capCandidata;

            if ($capacidadAcumulada >= $deficit) {
                break;
            }
        }

        return $secundariasParaUnir;
    }

    /**
     * Encuentra la asignación óptima de mesa (individual o unión de mesas) para un número de comensales.
     *
     * @param  Collection<int, Espacio>  $mesasDisponibles
     * @return array{
     *     mesa_principal: ?Espacio,
     *     mesas_unidas: array<int, Espacio>,
     *     requiere_union: bool,
     *     capacidad_total: int,
     *     zona: string
     * }
     */
    public function seleccionarMejorAsignacion(int $comensalesTotales, Collection $mesasDisponibles): array
    {
        $vacio = [
            'mesa_principal' => null,
            'mesas_unidas' => [],
            'requiere_union' => false,
            'capacidad_total' => 0,
            'zona' => 'Salón Principal',
        ];

        if ($mesasDisponibles->isEmpty() || $comensalesTotales <= 0) {
            return $vacio;
        }

        // 1. Intentar encontrar una sola mesa que satisfaga la capacidad
        $candidatasIndividuales = $mesasDisponibles->filter(function (Espacio $m) use ($comensalesTotales): bool {
            $cap = (int) ($m->capacidad_personas ?? 4);
            $meta = is_array($m->meta_datos) ? $m->meta_datos : [];
            $tipoMesa = (string) ($meta['tipo_mesa'] ?? '');

            // Si hay más de 1 persona, evitar barras o asientos individuales
            if ($comensalesTotales > 1 && ($tipoMesa === 'barra' || $cap === 1)) {
                return false;
            }

            return $cap >= $comensalesTotales;
        });

        if ($candidatasIndividuales->isNotEmpty()) {
            // Ordenar por menor desperdicio de capacidad (mejor ajuste) y luego orden natural
            /** @var Espacio $mejorMesa */
            $mejorMesa = $candidatasIndividuales->sort(function (Espacio $a, Espacio $b) use ($comensalesTotales): int {
                $capA = (int) ($a->capacidad_personas ?? 4);
                $capB = (int) ($b->capacidad_personas ?? 4);
                $diffA = $capA - $comensalesTotales;
                $diffB = $capB - $comensalesTotales;

                if ($diffA === $diffB) {
                    return (int) $a->id <=> (int) $b->id;
                }

                return $diffA <=> $diffB;
            })->first();

            $meta = is_array($mejorMesa->meta_datos) ? $mejorMesa->meta_datos : [];
            $ubicacionNombre = $mejorMesa->ubicacion !== null ? $mejorMesa->ubicacion->nombre : 'Salón Principal';
            $zona = $this->formatearZona($meta['zona_restaurante'] ?? $ubicacionNombre);

            return [
                'mesa_principal' => $mejorMesa,
                'mesas_unidas' => [],
                'requiere_union' => false,
                'capacidad_total' => (int) ($mejorMesa->capacidad_personas ?? 4),
                'zona' => $zona,
            ];
        }

        // 2. Si ninguna mesa individual alcanza, buscar unión inteligente en la misma zona
        // Agrupar mesas normales (no barras) por zona
        $mesasParaUnion = $mesasDisponibles->filter(function (Espacio $m): bool {
            $meta = is_array($m->meta_datos) ? $m->meta_datos : [];

            return ($meta['tipo_mesa'] ?? '') !== 'barra';
        });

        if ($mesasParaUnion->isEmpty()) {
            $mesasParaUnion = $mesasDisponibles;
        }

        $porZona = $mesasParaUnion->groupBy(function (Espacio $m): string {
            $meta = is_array($m->meta_datos) ? $m->meta_datos : [];
            $ubicacionNombre = $m->ubicacion !== null ? $m->ubicacion->nombre : 'interior';

            return (string) ($meta['zona_restaurante'] ?? $ubicacionNombre);
        });

        $mejorCombinacion = null;
        $menorExceso = PHP_INT_MAX;
        $menorCantidadMesas = PHP_INT_MAX;

        foreach ($porZona as $zonaKey => $mesasDeZona) {
            $ordenadas = $mesasDeZona->sortByDesc(fn (Espacio $m): int => (int) ($m->capacidad_personas ?? 4))->values();
            $acumuladas = [];
            $capAcumulada = 0;

            foreach ($ordenadas as $mesa) {
                $acumuladas[] = $mesa;
                $capAcumulada += (int) ($mesa->capacidad_personas ?? 4);

                if ($capAcumulada >= $comensalesTotales) {
                    $exceso = $capAcumulada - $comensalesTotales;
                    $cantMesas = count($acumuladas);

                    if ($cantMesas < $menorCantidadMesas || ($cantMesas === $menorCantidadMesas && $exceso < $menorExceso)) {
                        $menorCantidadMesas = $cantMesas;
                        $menorExceso = $exceso;
                        $mejorCombinacion = [
                            'mesa_principal' => $acumuladas[0],
                            'mesas_unidas' => $acumuladas,
                            'requiere_union' => count($acumuladas) > 1,
                            'capacidad_total' => $capAcumulada,
                            'zona' => $this->formatearZona((string) $zonaKey),
                        ];
                    }
                    break;
                }
            }
        }

        if ($mejorCombinacion !== null) {
            return $mejorCombinacion;
        }

        // 3. Fallback: primera mesa disponible
        /** @var Espacio $fallbackMesa */
        $fallbackMesa = $mesasDisponibles->first();
        $metaFallback = is_array($fallbackMesa->meta_datos) ? $fallbackMesa->meta_datos : [];

        return [
            'mesa_principal' => $fallbackMesa,
            'mesas_unidas' => [],
            'requiere_union' => false,
            'capacidad_total' => (int) ($fallbackMesa->capacidad_personas ?? 4),
            'zona' => $this->formatearZona($metaFallback['zona_restaurante'] ?? 'Salón Principal'),
        ];
    }

    private function formatearZona(string $zona): string
    {
        return match (strtolower(trim($zona))) {
            'interior' => 'Salón Interior',
            'terraza' => 'Terraza al Aire Libre',
            'bar' => 'Área de Barra',
            'vip' => 'Área VIP',
            default => ucfirst($zona),
        };
    }
}
