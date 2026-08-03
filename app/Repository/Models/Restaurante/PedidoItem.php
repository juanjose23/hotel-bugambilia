<?php

declare(strict_types=1);

namespace App\Repository\Models\Restaurante;

use App\Enums\Restaurante\AreaCocina;
use App\Enums\Restaurante\EstadoItemPedido;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $pedido_id
 * @property int|null $plato_id
 * @property AreaCocina|null $area_cocina
 * @property float $cantidad
 * @property float $precio_unitario
 * @property float $subtotal
 * @property EstadoItemPedido $estado
 * @property string|null $notas
 * @property string|null $observaciones
 * @property Plato|null $plato
 * @property Pedido|null $pedido
 */
final class PedidoItem extends Model
{
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
}
