<?php

declare(strict_types=1);

namespace App\Repository\Models\Restaurante;

use App\Enums\Restaurante\AreaCocina;
use App\Enums\Restaurante\EstadoItemPedido;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @property int $id
 * @property int $pedido_id
 * @property int|null $plato_id
 * @property string $tipo_item
 * @property int|null $producto_id
 * @property int|null $producto_variante_id
 * @property AreaCocina|null $area_cocina
 * @property float $cantidad
 * @property float $precio_unitario
 * @property float $subtotal
 * @property EstadoItemPedido $estado
 * @property array<int, array<string, mixed>>|null $bloqueo_stock_detalle
 * @property CarbonInterface|null $bloqueado_stock_en
 * @property string|null $notas
 * @property string|null $observaciones
 * @property Plato|null $plato
 * @property Producto|null $producto
 * @property ProductoVariante|null $variante
 * @property Pedido|null $pedido
 */
final class PedidoItem extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'pedido_items';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'estado' => EstadoItemPedido::class,
            'area_cocina' => AreaCocina::class,
            'cantidad' => 'decimal:2',
            'precio_unitario' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'bloqueo_stock_detalle' => 'array',
            'bloqueado_stock_en' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        self::creating(function (PedidoItem $item): void {
            if ($item->getAttribute('subtotal') === null || $item->subtotal == 0) {
                $item->subtotal = round($item->precio_unitario * $item->cantidad, 2);
            }

            if ($item->getAttribute('estado') === null) {
                $item->estado = EstadoItemPedido::PENDIENTE;
            }
        });

        self::updating(function (PedidoItem $item): void {
            $item->subtotal = round($item->precio_unitario * $item->cantidad, 2);
        });
    }

    /** @return BelongsTo<Pedido, $this> */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    /** @return BelongsTo<Plato, $this> */
    public function plato(): BelongsTo
    {
        return $this->belongsTo(Plato::class, 'plato_id');
    }

    /** @return BelongsTo<Producto, $this> */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /** @return BelongsTo<ProductoVariante, $this> */
    public function variante(): BelongsTo
    {
        return $this->belongsTo(ProductoVariante::class, 'producto_variante_id');
    }

    public function esProducto(): bool
    {
        return $this->tipo_item === 'producto' || ! empty($this->producto_id);
    }

    public function esPlato(): bool
    {
        return $this->tipo_item === 'plato' || ! empty($this->plato_id);
    }

    public function tieneVariante(): bool
    {
        return ! empty($this->producto_variante_id);
    }
}
