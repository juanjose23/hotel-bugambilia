<?php

namespace App\Models\Compras;

use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\ProductoVariante;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @property int $id
 * @property int $solicitud_id
 * @property int $producto_id
 * @property int|null $producto_variante_id
 * @property int|null $unidad_medida_id
 * @property float $cantidad_solicitada
 * @property float $cantidad_aprobada
 * @property string|null $observaciones
 */
class SolicitudItem extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'solicitud_items';

    protected $with = [
        'producto',
        'variante',
        'unidadMedida',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'cantidad_solicitada' => 'decimal:2',
        'cantidad_aprobada' => 'decimal:2',
    ];

    /** @return BelongsTo<Solicitud, $this> */
    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class, 'solicitud_id');
    }

    /** @return BelongsTo<Producto, $this> */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    /** @return BelongsTo<ProductoVariante, $this> */
    public function productoVariante(): BelongsTo
    {
        return $this->belongsTo(ProductoVariante::class);
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
