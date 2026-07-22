<?php

declare(strict_types=1);

namespace App\Repository\Models\Compras;

use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use Database\Factories\Compras\OrdenCompraItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @use HasFactory<OrdenCompraItemFactory>
 */
final class OrdenCompraItem extends Model implements AuditableContract
{
    /** @phpstan-ignore missingType.generics */
    use Auditable, HasFactory, SoftDeletes;

    protected static function newFactory(): OrdenCompraItemFactory
    {
        return OrdenCompraItemFactory::new();
    }

    protected $table = 'orden_compra_items';

    protected $with = [
        'producto',
        'variante',
        'unidadMedida',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'cantidad' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /** @return BelongsTo<OrdenCompra, $this> */
    public function ordenCompra(): BelongsTo
    {
        return $this->belongsTo(OrdenCompra::class);
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

    /** @return HasMany<RecepcionItem, $this> */
    public function recepcionItems(): HasMany
    {
        return $this->hasMany(RecepcionItem::class, 'orden_item_id');
    }
}
