<?php

declare(strict_types=1);

namespace App\Repository\Models\Restaurante;

use App\Enums\Restaurante\EstadoItemPedido;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $pedido_id
 * @property int|null $plato_id
 * @property float $cantidad
 * @property float $precio_unitario
 * @property float $subtotal
 * @property EstadoItemPedido $estado
 * @property string|null $notas
 */
class PedidoItem extends Model
{
    protected $table = 'pedido_items';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'estado' => EstadoItemPedido::class,
            'cantidad' => 'decimal:2',
            'precio_unitario' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
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
