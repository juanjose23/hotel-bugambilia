<?php

declare(strict_types=1);

namespace App\Repository\Models\Compras;

use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Inventario\Lote;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

final class DevolucionItem extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'devolucion_items';

    protected $guarded = ['id'];

    protected $casts = [
        'cantidad_devolver' => 'float',
    ];

    protected static function booted(): void
    {
        self::saving(function (self $item) {

            if ($item->lote_id && ! $item->producto_id) {
                $lote = $item->lote;
                if ($lote) {
                    $item->producto_id = $lote->producto_id;
                    $item->producto_variante_id = $lote->producto_variante_id;

                    /** @var int|null $unidadMedidaId */
                    $unidadMedidaId = $lote->recepcionItem->unidad_medida_id
                        ?? $lote->producto?->unidad_medida_id;
                    if (is_int($unidadMedidaId) && $unidadMedidaId >= 0) {
                        /** @var int<0, max> $unidadMedidaId */
                        $item->unidad_medida_id = $unidadMedidaId;
                    } else {
                        $item->unidad_medida_id = null;
                    }
                    if (! $item->recepcion_item_id) {
                        $item->recepcion_item_id = $lote->recepcion_item_id;
                    }
                }
            } elseif ($item->recepcion_item_id && ! $item->producto_id) {
                $recepcionItem = $item->recepcionItem;
                if ($recepcionItem) {
                    $item->producto_id = $recepcionItem->producto_id;
                    $item->producto_variante_id = $recepcionItem->producto_variante_id;
                    $item->unidad_medida_id = (int) $recepcionItem->unidad_medida_id;
                }
            }
        });
    }

    /** @return BelongsTo<DevolucionCompra, $this> */
    public function devolucion(): BelongsTo
    {
        return $this->belongsTo(DevolucionCompra::class, 'devolucion_id');
    }

    /** @return BelongsTo<Lote, $this> */
    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }

    /** @return BelongsTo<RecepcionItem, $this> */
    public function recepcionItem(): BelongsTo
    {
        return $this->belongsTo(RecepcionItem::class, 'recepcion_item_id');
    }

    /** @return BelongsTo<Producto, $this> */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    /** @return BelongsTo<ProductoVariante, $this> */
    public function variante(): BelongsTo
    {
        return $this->belongsTo(ProductoVariante::class, 'producto_variante_id');
    }

    /** @return BelongsTo<Catalogo, $this> */
    public function unidadMedida(): BelongsTo
    {
        return $this->belongsTo(Catalogo::class, 'unidad_medida_id');
    }
}
