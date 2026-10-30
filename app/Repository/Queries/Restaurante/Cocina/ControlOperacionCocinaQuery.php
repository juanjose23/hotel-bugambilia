<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Cocina;

use App\Enums\Inventario\EstadoLote;
use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Restaurante\Buffet;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Models\Restaurante\ProcesoCocina;
use App\Repository\Models\Shared\Stock;
use App\Repository\Persistencia\Restaurante\ExistenciasCocinaRepositorio;

final readonly class ControlOperacionCocinaQuery
{
    public function __construct(private ExistenciasCocinaRepositorio $existencias) {}

    /** @return array<int, string> */
    public function ubicaciones(): array
    {
        $opciones = [];
        foreach (Ubicacion::query()->where('estado', 1)->orderBy('nombre')->get() as $ubicacion) {
            $opciones[$ubicacion->id] = $ubicacion->nombre;
        }

        return $opciones;
    }

    /** @return array<int, string> */
    public function productos(?int $ubicacionId): array
    {
        if ($ubicacionId === null) {
            return [];
        }
        $stocks = $this->existencias->disponibles()->where('stockable_type', Ubicacion::class)->where('stockable_id', $ubicacionId)->whereNotNull('producto_variante_id')->with('variante')->get();
        $ids = $stocks->map(fn (Stock $stock): ?int => $stock->variante?->producto_id)->filter()->unique()->all();

        $opciones = [];
        foreach (Producto::query()->whereIn('id', $ids)->orderBy('nombre')->get() as $producto) {
            $opciones[$producto->id] = $producto->nombre;
        }

        return $opciones;
    }

    /** @return array<int, string> */
    public function variantes(?int $productoId, ?int $ubicacionId): array
    {
        if ($productoId === null || $ubicacionId === null) {
            return [];
        }
        $variantes = ProductoVariante::query()->with(['unidadMedida', 'producto.unidadMedida'])->where('producto_id', $productoId)->orderBy('nombre_variante')->get();
        $opciones = [];
        foreach ($variantes as $variante) {
            $cantidad = $this->existencias->cantidad($this->existencias->porVariante($variante->id, $ubicacionId));
            if ($cantidad <= 0) {
                continue;
            }
            $unidad = $variante->unidadMedida->nombre ?? $variante->producto->unidadMedida->nombre ?? 'unidades';
            $opciones[$variante->id] = $variante->nombre_variante.' · '.number_format($cantidad, 3, ',', '.').' '.$unidad.' disponibles';
        }

        return $opciones;
    }

    /** @return array{cantidad: float, unidad: string, nombre: string, ubicacion: string} */
    public function seleccion(?int $varianteId, ?int $ubicacionId): array
    {
        $variante = $varianteId !== null ? ProductoVariante::query()->with(['producto.unidadMedida', 'unidadMedida'])->find($varianteId) : null;
        $ubicacion = $ubicacionId !== null ? Ubicacion::query()->find($ubicacionId) : null;

        return ['cantidad' => $varianteId !== null && $ubicacionId !== null ? $this->existencias->cantidad($this->existencias->porVariante($varianteId, $ubicacionId)) : 0.0,
            'unidad' => $variante->unidadMedida->nombre ?? $variante->producto->unidadMedida->nombre ?? 'unidades',
            'nombre' => $variante !== null ? ($variante->producto->nombre ?? '').' · '.$variante->nombre_variante : 'Selecciona una presentación',
            'ubicacion' => $ubicacion->nombre ?? 'Selecciona una bodega'];
    }

    /** @return array{pedidos: int, bloqueados: int, borradores: int, buffets: int, por_vencer: int, vencidos: int, reponer: int, alertas: list<array{producto: string, ubicacion: string, cantidad: float, unidad: string, mensaje: string, nivel: string}>} */
    public function resumen(): array
    {
        $stocks = Stock::query()->where('stockable_type', Ubicacion::class)->where(function ($query): void {
            $query->where('cantidad_actual', '>', 0)->orWhere('cantidad_ideal', '>', 0);
        })->with(['variante.producto.unidadMedida', 'variante.unidadMedida', 'producto.unidadMedida', 'lote'])->orderBy('id')->get();
        $alertas = [];
        $vence = 0;
        $vencidos = 0;
        $reponer = 0;
        $ubicaciones = $this->ubicaciones();
        foreach ($stocks as $stock) {
            $fecha = $stock->lote?->fecha_vencimiento;
            $pasado = $fecha !== null && $fecha->toDateString() < today()->toDateString();
            if ($stock->cantidad_actual <= 0) {
                continue;
            }
            if ($pasado) {
                $vencidos++;
            }
            if (! $pasado && $fecha !== null && $fecha->toDateString() <= today()->addDays(2)->toDateString()) {
                $vence++;
            }
            if ($pasado || ($fecha !== null && $fecha->toDateString() <= today()->addDays(2)->toDateString())) {
                $alertas[] = ['producto' => $stock->variante->producto->nombre ?? $stock->producto->nombre ?? 'Producto', 'ubicacion' => $ubicaciones[(int) $stock->stockable_id] ?? 'Bodega', 'cantidad' => (float) $stock->cantidad_actual,
                    'unidad' => $stock->variante->unidadMedida->nombre ?? $stock->variante->producto->unidadMedida->nombre ?? $stock->producto->unidadMedida->nombre ?? 'unidades',
                    'mensaje' => $pasado ? 'Vencido · separar y revisar' : 'Vence '.$fecha->format('d/m'), 'nivel' => $pasado ? 'danger' : 'warning'];
            }
        }
        foreach ($stocks->groupBy(fn (Stock $stock): string => $stock->stockable_id.':'.($stock->producto_variante_id ?? 'p'.$stock->producto_id)) as $grupo) {
            $ideal = 0.0;
            $cantidad = 0.0;
            foreach ($grupo as $stock) {
                $ideal = max($ideal, (float) $stock->cantidad_ideal);
                if ($stock->lote === null || ($stock->lote->estado === EstadoLote::Disponible && ($stock->lote->fecha_vencimiento === null || $stock->lote->fecha_vencimiento->toDateString() >= today()->toDateString()))) {
                    $cantidad += (float) $stock->cantidad_actual;
                }
            }
            if ($ideal <= 0 || $cantidad >= $ideal) {
                continue;
            }
            $reponer++;
            $primero = $grupo->first();
            if ($primero === null) {
                continue;
            }
            $alertas[] = ['producto' => $primero->variante->producto->nombre ?? $primero->producto->nombre ?? 'Producto', 'ubicacion' => $ubicaciones[(int) $primero->stockable_id] ?? 'Bodega', 'cantidad' => $cantidad,
                'unidad' => $primero->variante->unidadMedida->nombre ?? $primero->variante->producto->unidadMedida->nombre ?? $primero->producto->unidadMedida->nombre ?? 'unidades', 'mensaje' => 'Reponer hasta '.number_format($ideal, 2, ',', '.'), 'nivel' => 'info'];
        }

        return ['pedidos' => Pedido::query()->whereIn('estado', [EstadoPedido::ABIERTO, EstadoPedido::EN_PREPARACION, EstadoPedido::LISTO])->count(), 'bloqueados' => PedidoItem::query()->where('estado', EstadoItemPedido::BLOQUEADO_STOCK)->count(),
            'borradores' => ProcesoCocina::query()->where('tipo_registro', 'borrador')->whereNull('confirmado_at')->count(), 'buffets' => Buffet::query()->where('estado', 'abierto')->whereDate('fecha_servicio', today())->count(),
            'por_vencer' => $vence, 'vencidos' => $vencidos, 'reponer' => $reponer, 'alertas' => array_slice($alertas, 0, 12)];
    }
}
