<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cocina;

use App\Enums\Compras\EstadoSolicitud;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Compras\Solicitud;
use App\Repository\Models\Compras\SolicitudItem;
use App\Repository\Models\Shared\Stock;
use App\Repository\Models\User;
use App\Repository\Persistencia\Restaurante\ExistenciasCocinaRepositorio;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class DespacharSolicitudAbastecimientoCocina
{
    public function __construct(
        private RestauranteRepositorioInterface $repositorio,
        private TrasladarExistenciasCocina $trasladar,
        private ExistenciasCocinaRepositorio $existencias,
    ) {}

    /** @param array<int, array<array{stock_id: int|string, cantidad: float|int|string}>>|null $lotes */
    public function ejecutar(
        Solicitud $solicitud,
        int $ubicacionOrigenId,
        ?int $ubicacionDestinoId = null,
        ?int $usuarioId = null,
        ?array $lotes = null,
        ?string $motivo = null,
    ): Solicitud {
        return DB::transaction(function () use ($solicitud, $ubicacionOrigenId, $ubicacionDestinoId, $usuarioId, $lotes, $motivo): Solicitud {
            $solicitud = $this->repositorio->solicitudConLock($solicitud->id);
            if ($this->repositorio->solicitudYaAbastecida($solicitud->id)) {
                throw new DomainException('La solicitud ya fue abastecida; no se puede repetir el despacho.');
            }
            $solicitud->load(['items.producto', 'items.variante']);

            if ($solicitud->estado !== EstadoSolicitud::Aprobada) {
                throw new DomainException('Solo se puede despachar una solicitud aprobada.');
            }

            $this->validarAutorizacionInventario($usuarioId);

            $destinoId = $ubicacionDestinoId ?? $this->repositorio->obtenerUbicacionCocinaId();
            $origen = $this->repositorio->obtenerUbicacionPorId($ubicacionOrigenId);
            $destino = $this->repositorio->obtenerUbicacionPorId($destinoId);

            if (! $origen instanceof Ubicacion || ! $destino instanceof Ubicacion) {
                throw new DomainException('Seleccione ubicación origen y destino válidas.');
            }

            $this->existencias->bloquearUbicaciones([(int) $origen->id, (int) $destino->id]);
            foreach ($solicitud->items as $item) {
                $this->despacharItem($solicitud, $item, (int) $origen->id, (int) $destino->id, $usuarioId, $lotes === null ? null : ($lotes[$item->id] ?? []), $motivo);
            }

            $nota = '['.now()->format('d/m/Y H:i')."] DESPACHO COCINA: {$origen->nombre} -> {$destino->nombre}";
            if (trim($motivo ?? '') !== '') {
                $nota .= ' | Cambio de lote: '.trim($motivo);
            }
            $solicitud->notas = trim((string) ($solicitud->notas ?? '')."\n".$nota);
            $this->repositorio->guardarSolicitudAbastecimiento($solicitud);

            return $solicitud->refresh();
        });
    }

    private function validarAutorizacionInventario(?int $usuarioId): void
    {
        $usuario = $usuarioId !== null ? $this->repositorio->obtenerUsuarioPorId($usuarioId) : null;

        if (! $usuario instanceof User || ! $usuario->can('Inventario:ResolverAbastecimientoCocina')) {
            throw new DomainException('Debe autorizar este despacho un usuario con permiso de inventario.');
        }
    }

    /** @return array<int, string> */
    public function opciones(SolicitudItem $item, int $origenId): array
    {
        return $this->existencias->porVariante((int) $item->producto_variante_id, $origenId)
            ->mapWithKeys(static function (Stock $stock): array {
                $codigo = $stock->lote->codigo_lote ?? 'Sin lote';
                $vence = $stock->lote?->fecha_vencimiento?->format('d/m/Y') ?? 'Sin vencimiento';

                return [$stock->id => "{$codigo} · {$vence} · Saldo {$stock->cantidad_actual}"];
            })->all();
    }

    /** @return list<array{stock_id: int, cantidad: float}> */
    public function sugerencia(SolicitudItem $item, int $origenId, bool $bloquear = false): array
    {
        $pendiente = $this->cantidad($item);
        $filas = [];
        foreach ($this->existencias->porVariante((int) $item->producto_variante_id, $origenId, $bloquear) as $stock) {
            if ($pendiente <= 0.00001) {
                break;
            }

            $retiro = round(min($pendiente, (float) $stock->cantidad_actual), 4);
            $filas[] = ['stock_id' => $stock->id, 'cantidad' => $retiro];
            $pendiente = round($pendiente - $retiro, 4);
        }

        return $filas;
    }

    public function cantidad(SolicitudItem $item): float
    {
        $aprobada = (float) ($item->cantidad_aprobada ?? 0);

        return $aprobada > 0 ? $aprobada : (float) $item->cantidad_solicitada;
    }

    /** @param array<array{stock_id: int|string, cantidad: float|int|string}>|null $lotes */
    private function despacharItem(Solicitud $solicitud, SolicitudItem $item, int $origenId, int $destinoId, ?int $usuarioId, ?array $lotes, ?string $motivo): void
    {
        $varianteId = (int) ($item->producto_variante_id ?? 0);
        $productoId = (int) ($item->producto_id ?? 0);
        $cantidad = $this->cantidad($item);

        if ($varianteId <= 0 || $productoId <= 0 || $cantidad <= 0) {
            return;
        }

        $contexto = ['documento_tipo' => 'solicitud_abastecimiento_cocina', 'documento_id' => $solicitud->id, 'referencia' => 'Despacho de '.$solicitud->codigo, 'creado_por_id' => $usuarioId];
        if ($lotes === null) {
            $this->trasladar->ejecutar($varianteId, $origenId, $destinoId, $cantidad, $contexto);

            return;
        }

        $confirmados = [];
        foreach ($lotes as $fila) {
            $stockId = (int) $fila['stock_id'];
            $retiro = round((float) $fila['cantidad'], 4);
            if ($stockId <= 0 || ! is_finite($retiro) || $retiro <= 0 || isset($confirmados[$stockId])) {
                throw new DomainException('Indique un lote distinto y una cantidad positiva en cada línea.');
            }
            $confirmados[$stockId] = $retiro;
        }
        if (abs(array_sum($confirmados) - $cantidad) > 0.00001) {
            throw new DomainException('Los lotes confirmados deben cubrir la cantidad aprobada.');
        }

        $sugeridos = array_column($this->sugerencia($item, $origenId, true), 'cantidad', 'stock_id');
        if ($confirmados != $sugeridos && trim($motivo ?? '') === '') {
            throw new DomainException('Indique el motivo si retira lotes distintos de los sugeridos.');
        }
        foreach ($confirmados as $stockId => $retiro) {
            $this->trasladar->ejecutar($varianteId, $origenId, $destinoId, $retiro, $contexto, $stockId);
        }
    }
}
