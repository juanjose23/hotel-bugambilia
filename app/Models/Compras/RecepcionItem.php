<?php

namespace App\Models\Compras;

use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\ProductoVariante;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecepcionItem extends Model
{
    use SoftDeletes;

    protected $table = 'recepcion_items';

    protected $fillable = [
        'recepcion_id',
        'orden_item_id',
        'producto_id',
        'producto_variante_id',
        'unidad_medida_id',
        'cantidad_recibida',
        'cantidad_rechazada',
        'motivo_rechazo',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            if ($item->orden_item_id && ! $item->producto_id) {
                $ordenItem = $item->ordenItem;
                if ($ordenItem) {
                    $item->producto_id = $ordenItem->producto_id;
                    $item->producto_variante_id = $ordenItem->producto_variante_id;
                    $item->unidad_medida_id = $ordenItem->unidad_medida_id;
                }
            }
        });
    }

    /** @return BelongsTo<RecepcionCompra, $this> */
    public function recepcion(): BelongsTo
    {
        return $this->belongsTo(RecepcionCompra::class, 'recepcion_id');
    }

    /** @return BelongsTo<OrdenCompraItem, $this> */
    public function ordenItem(): BelongsTo
    {
        return $this->belongsTo(OrdenCompraItem::class, 'orden_item_id');
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
