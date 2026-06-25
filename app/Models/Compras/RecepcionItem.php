<?php

namespace App\Models\Compras;

use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\ProductoVariante;
use App\Models\Catalogos\Ubicacion;
use App\Models\Inventario\Lote;
use Database\Factories\Compras\RecepcionItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class RecepcionItem extends Model implements AuditableContract
{
    /** @use HasFactory<RecepcionItemFactory> */
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'recepcion_items';

    protected $casts = [
        'fecha_vencimiento' => 'date:Y-m-d',
    ];

    protected $guarded = ['id'];

    protected static function booted(): void
    {
        static::saving(function (self $item) {
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

    /** @return BelongsTo<Ubicacion, $this> */
    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_id');
    }

    /** @return BelongsTo<Catalogo, $this> */
    public function unidadMedida(): BelongsTo
    {
        return $this->belongsTo(Catalogo::class, 'unidad_medida_id');
    }

    /** @return HasOne<Lote, $this> */
    public function lote(): HasOne
    {
        return $this->hasOne(Lote::class, 'recepcion_item_id');
    }

    /** @return HasMany<Lote, $this> */
    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class, 'recepcion_item_id');
    }
}
