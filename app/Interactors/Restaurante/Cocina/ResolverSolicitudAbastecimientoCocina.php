<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cocina;

use App\Enums\Compras\EstadoSolicitud;
use App\Notifications\Compras\NotificadorCompras;
use App\Repository\Models\Compras\Solicitud;
use App\Repository\Models\Compras\SolicitudItem;
use App\Repository\Models\Shared\Stock;
use App\Repository\Models\User;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final readonly class ResolverSolicitudAbastecimientoCocina
{
    public function __construct(
        private RestauranteRepositorioInterface $repositorio,
        private NotificadorCompras $notificadorCompras,
        private TrasladarExistenciasCocina $trasladar,
    ) {}

    public function ejecutar(Solicitud $solicitud, ?int $usuarioId = null): Solicitud
    {
        return DB::transaction(function () use ($solicitud, $usuarioId): Solicitud {
            $solicitud = $this->repositorio->solicitudConLock($solicitud->id);
            if ($this->repositorio->solicitudYaAbastecida($solicitud->id)) {
                throw new DomainException('La solicitud ya fue abastecida; no se puede repetir el despacho.');
            }
            $solicitud->load(['items.producto', 'items.variante.producto']);

            if ($solicitud->estado !== EstadoSolicitud::Aprobada) {
                throw new DomainException('Solo se puede resolver inventario de una solicitud aprobada.');
            }

            $this->validarAutorizacionInventario($usuarioId);

            $destinoId = $this->repositorio->obtenerUbicacionCocinaId();
            $faltantes = $this->faltantesInventarioInterno($solicitud, $destinoId);

            if ($faltantes !== []) {
                $this->notificadorCompras->abastecimientoCocinaRequiereCompra($solicitud, $faltantes);

                throw new DomainException(
                    'Inventario interno insuficiente. Genere cotización / orden de compra para: '.implode('; ', $faltantes).'.'
                );
            }

            $traslados = [];

            foreach ($solicitud->items as $item) {
                $traslados = [
                    ...$traslados,
                    ...$this->resolverItemDesdeBodegas($solicitud, $item, $destinoId, $usuarioId),
                ];
            }

            $destino = $this->repositorio->obtenerUbicacionPorId($destinoId);
            $detalle = implode('; ', array_slice($traslados, 0, 8));
            $nota = '['.now()->format('d/m/Y H:i').'] RESUELTO CON INVENTARIO: '.$detalle;
            $solicitud->notas = trim((string) ($solicitud->notas ?? '')."\n".$nota);
            $this->repositorio->guardarSolicitudAbastecimiento($solicitud);

            $this->notificadorCompras->abastecimientoCocinaResueltoConInventario($solicitud, $traslados);

            return $solicitud->refresh();
        });
    }

    private function validarAutorizacionInventario(?int $usuarioId): void
    {
        $usuario = $usuarioId !== null ? $this->repositorio->obtenerUsuarioPorId($usuarioId) : null;

        if (! $usuario instanceof User || ! $usuario->can('Inventario:ResolverAbastecimientoCocina')) {
            throw new DomainException('Debe autorizar esta resolución un usuario con permiso de inventario.');
        }
    }

    /**
     * @return list<string>
     */
    private function faltantesInventarioInterno(Solicitud $solicitud, int $destinoId): array
    {
        $faltantes = [];

        foreach ($solicitud->items as $item) {
            $varianteId = (int) ($item->producto_variante_id ?? 0);
            $cantidad = $this->cantidadAResolver($item);

            if ($varianteId <= 0 || $cantidad <= 0) {
                continue;
            }

            $sum = $this->stocksDisponibles($varianteId, $destinoId)->sum('cantidad_actual');
            $disponible = is_numeric($sum) ? (float) $sum : 0.0;

            if ($disponible < $cantidad) {
                $faltantes[] = sprintf(
                    '%s disponible %s / requerido %s',
                    $this->nombreItem($item),
                    number_format($disponible, 2),
                    number_format($cantidad, 2),
                );
            }
        }

        return $faltantes;
    }

    /**
     * @return list<string>
     */
    private function resolverItemDesdeBodegas(Solicitud $solicitud, SolicitudItem $item, int $destinoId, ?int $usuarioId): array
    {
        $varianteId = (int) ($item->producto_variante_id ?? 0);
        $productoId = (int) ($item->producto_id ?? 0);
        $pendiente = $this->cantidadAResolver($item);
        $traslados = [];

        if ($varianteId <= 0 || $productoId <= 0 || $pendiente <= 0) {
            return [];
        }

        $destino = $this->repositorio->obtenerUbicacionPorId($destinoId);
        $destinoNombre = $destino->nombre ?? 'Cocina';

        foreach ($this->stocksDisponibles($varianteId, $destinoId) as $stockOrigen) {
            if ($pendiente <= 0) {
                break;
            }

            $cantidad = min($pendiente, (float) $stockOrigen->cantidad_actual);

            if ($cantidad <= 0) {
                continue;
            }

            $origenId = (int) $stockOrigen->stockable_id;
            $origen = $this->repositorio->obtenerUbicacionPorId($origenId);
            $origenNombre = $origen->nombre ?? "Bodega #{$origenId}";
            $this->trasladar->ejecutar($varianteId, $origenId, $destinoId, $cantidad, ['documento_tipo' => 'solicitud_abastecimiento_cocina', 'documento_id' => $solicitud->id, 'referencia' => 'Resolución de '.$solicitud->codigo, 'creado_por_id' => $usuarioId]);

            $pendiente -= $cantidad;
            $traslados[] = sprintf(
                '%s -> %s: %s x %s',
                $origenNombre,
                $destinoNombre,
                $this->nombreItem($item),
                number_format($cantidad, 2),
            );
        }

        return $traslados;
    }

    /**
     * @return Collection<int, Stock>
     */
    private function stocksDisponibles(int $varianteId, int $destinoId): Collection
    {
        return $this->repositorio->obtenerStocksDisponiblesParaTraslado($varianteId, $destinoId);
    }

    private function cantidadAResolver(SolicitudItem $item): float
    {
        $aprobada = (float) ($item->cantidad_aprobada ?? 0);

        return $aprobada > 0 ? $aprobada : (float) $item->cantidad_solicitada;
    }

    private function nombreItem(SolicitudItem $item): string
    {
        $producto = $item->variante !== null ? ($item->variante->producto->nombre ?? 'Producto') : ($item->producto->nombre ?? 'Producto');
        $variante = $item->variante !== null ? ($item->variante->nombre_variante ?: $item->variante->codigo) : null;

        return $variante !== null ? "{$producto} - {$variante}" : $producto;
    }
}
