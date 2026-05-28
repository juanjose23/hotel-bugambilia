<?php

namespace App\Models\Compras;

use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\ProductoVariante;
use App\Models\Inventario\Lote;
use Database\Factories\Compras\DevolucionItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class DevolucionItem extends Model implements AuditableContract
{
    /** @use HasFactory<DevolucionItemFactory> */
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'devolucion_items';

    protected $with = [
        'producto',
        'variante',
        'unidadMedida',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'cantidad_devolver' => 'float',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            // Auto fill producto, variante and unidad_medida from lote or recepcion_item if missing
            if ($item->lote_id && ! $item->producto_id) {
                $lote = $item->lote;
                if ($lote) {
                    $item->producto_id = $lote->producto_id;
                    $item->producto_variante_id = $lote->producto_variante_id;

                    // Fallback to product unit if no recepcion item
                    $item->unidad_medida_id = $lote->recepcionItem->unidad_medida_id
                        ?? $lote->producto->unidad_medida_id ?? null;

                    if (! $item->recepcion_item_id) {
                        $item->recepcion_item_id = $lote->recepcion_item_id;
                    }
                }
            } elseif ($item->recepcion_item_id && ! $item->producto_id) {
                $recepcionItem = $item->recepcionItem;
                if ($recepcionItem) {
                    $item->producto_id = $recepcionItem->producto_id;
                    $item->producto_variante_id = $recepcionItem->producto_variante_id;
                    $item->unidad_medida_id = $recepcionItem->unidad_medida_id;
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
