<?php

declare(strict_types=1);

namespace App\Repository\Models\Limpieza;

use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class SustitucionStock extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'limp_sustituciones_stock';

    protected $guarded = ['id'];

    protected $casts = [
        'ejecucion_id' => 'integer',
        'producto_id' => 'integer',
        'sustituto_producto_id' => 'integer',
        'producto_variante_id' => 'integer',
        'sustituto_variante_id' => 'integer',
        'cantidad' => 'decimal:4',
    ];

    /**
     * @return BelongsTo<LimpiezaEjecucion, $this>
     */
    public function ejecucion(): BelongsTo
    {
        return $this->belongsTo(LimpiezaEjecucion::class, 'ejecucion_id');
    }

    /**
     * @return BelongsTo<Producto, $this>
     */
    public function productoOriginal(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /**
     * @return BelongsTo<Producto, $this>
     */
    public function productoSustituto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'sustituto_producto_id');
    }

    /**
     * @return BelongsTo<ProductoVariante, $this>
     */
    public function varianteOriginal(): BelongsTo
    {
        return $this->belongsTo(ProductoVariante::class, 'producto_variante_id');
    }

    /**
     * @return BelongsTo<ProductoVariante, $this>
     */
    public function varianteSustituta(): BelongsTo
    {
        return $this->belongsTo(ProductoVariante::class, 'sustituto_variante_id');
    }
}
