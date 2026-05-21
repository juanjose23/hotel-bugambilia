<?php

namespace App\Models\Compras;

use App\Models\Catalogos\Producto;
use App\Models\Catalogos\ProductoVariante;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @property int $id
 * @property int $cotizacion_id
 * @property int $producto_id
 * @property int|null $producto_variante_id
 * @property int|null $unidad_medida_id
 * @property float $cantidad
 * @property float $precio_unitario
 * @property float $subtotal
 * @property bool $es_elegido
 */
class CotizacionItem extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'cotizacion_items';

    protected $with = [
        'producto',
        'variante',
    ];

    protected $fillable = [
        'cotizacion_id',
        'producto_id',
        'producto_variante_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /** @return BelongsTo<Cotizacion, $this> */
    public function cotizacion(): BelongsTo
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id');
    }

    /** @return BelongsTo<Producto, $this> */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /** @return BelongsTo<ProductoVariante, $this> */
    public function productoVariante(): BelongsTo
    {
        return $this->belongsTo(ProductoVariante::class, 'producto_variante_id');
    }

    /** @return BelongsTo<ProductoVariante, $this> */
    public function variante(): BelongsTo
    {
        return $this->belongsTo(ProductoVariante::class, 'producto_variante_id');
    }
}
