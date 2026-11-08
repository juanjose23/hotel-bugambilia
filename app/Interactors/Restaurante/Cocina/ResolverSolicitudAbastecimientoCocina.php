<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cocina;

use App\Enums\Compras\EstadoSolicitud;
use App\Notifications\Compras\NotificadorCompras;
use App\Repository\Models\Compras\Solicitud;
use App\Repository\Models\Compras\SolicitudItem;
use App\Repository\Models\User;
use App\Repository\Persistencia\Restaurante\ExistenciasCocinaRepositorio;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class ResolverSolicitudAbastecimientoCocina
{
    public function __construct(
        private RestauranteRepositorioInterface $repositorio,
        private NotificadorCompras $notificadorCompras,
        private TrasladarExistenciasCocina $trasladar,
        private ExistenciasCocinaRepositorio $existencias,
    ) {}

    public function ejecutar(Solicitud $solicitud, ?int $usuarioId = null, ?string $confirmacion = null): Solicitud
    {
        return DB::transaction(function () use ($solicitud, $usuarioId, $confirmacion): Solicitud {
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
            $plan = $this->plan($solicitud, $destinoId);
            if ($plan['faltantes'] !== []) {
                $this->notificadorCompras->abastecimientoCocinaRequiereCompra($solicitud, $plan['faltantes']);

                throw new DomainException(
                    'Inventario interno insuficiente. Genere cotización / orden de compra para: '.implode('; ', $plan['faltantes']).'.'
                );
            }
            if ($confirmacion !== null && ! hash_equals($plan['confirmacion'], $confirmacion)) {
                throw new DomainException('Los lotes sugeridos cambiaron. Vuelva a abrir el despacho y revise la lista de retiro.');
            }

            $this->existencias->bloquearUbicaciones([$destinoId, ...array_column($plan['filas'], 'origen_id')]);
            $traslados = [];
            foreach ($plan['filas'] as $fila) {
                $this->trasladar->ejecutar($fila['variante_id'], $fila['origen_id'], $destinoId, $fila['cantidad'], [
                    'documento_tipo' => 'solicitud_abastecimiento_cocina',
                    'documento_id' => $solicitud->id,
                    'referencia' => 'Resolución de '.$solicitud->codigo,
                    'creado_por_id' => $usuarioId,
                ], $fila['stock_id']);
                $traslados[] = $fila['texto'];
            }
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
     * @return array{filas: list<array{variante_id: int, origen_id: int, stock_id: int, cantidad: float, texto: string}>, faltantes: list<string>, confirmacion: string}
     */
    public function plan(Solicitud $solicitud, ?int $destinoId = null): array
    {
        $destinoId ??= $this->repositorio->obtenerUbicacionCocinaId();
        $destino = $this->repositorio->obtenerUbicacionPorId($destinoId);
        $solicitud->loadMissing(['items.producto', 'items.variante.producto']);
        $filas = [];
        $faltantes = [];
        $asignados = [];
        foreach ($solicitud->items as $item) {
            $varianteId = (int) ($item->producto_variante_id ?? 0);
            $pendiente = $requerido = $this->cantidadAResolver($item);
            if ($varianteId <= 0 || $pendiente <= 0 || (int) $item->producto_id <= 0) {
                continue;
            }

            foreach ($this->repositorio->obtenerStocksDisponiblesParaTraslado($varianteId, $destinoId) as $stock) {
                $cantidad = round(min($pendiente, (float) $stock->cantidad_actual - ($asignados[$stock->id] ?? 0.0)), 4);
                if ($cantidad <= 0) {
                    continue;
                }

                $origenId = (int) $stock->stockable_id;
                $origen = $this->repositorio->obtenerUbicacionPorId($origenId);
                $codigo = $stock->lote->codigo_lote ?? 'Sin lote';
                $vence = $stock->lote?->fecha_vencimiento?->format('d/m/Y') ?? 'Sin vencimiento';
                $filas[] = [
                    'variante_id' => $varianteId,
                    'origen_id' => $origenId,
                    'stock_id' => $stock->id,
                    'cantidad' => $cantidad,
                    'texto' => sprintf('%s → %s · %s · %s · %s · Retirar %s', $origen->nombre ?? 'Bodega', $destino->nombre ?? 'Cocina', $this->nombreItem($item), $codigo, $vence, $cantidad),
                ];
                $asignados[$stock->id] = ($asignados[$stock->id] ?? 0.0) + $cantidad;
                $pendiente = round($pendiente - $cantidad, 4);
                if ($pendiente <= 0.00001) {
                    break;
                }
            }

            if ($pendiente > 0.00001) {
                $faltantes[] = sprintf('%s disponible %s / requerido %s', $this->nombreItem($item), $requerido - $pendiente, $requerido);
            }
        }

        return [
            'filas' => $filas,
            'faltantes' => $faltantes,
            'confirmacion' => hash('sha256', json_encode([$solicitud->id, $destinoId, $filas, $faltantes], JSON_THROW_ON_ERROR)),
        ];
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
