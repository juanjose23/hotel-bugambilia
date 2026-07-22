<?php

declare(strict_types=1);

namespace App\Repository\Models\Inventario;

use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Catalogos\Ubicacion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Stock extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'inv_stock';

    protected $guarded = ['id'];

    protected $casts = [
        'cantidad' => 'float',
    ];

    /**
     * @return BelongsTo<Producto, $this>
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /**
     * @return BelongsTo<ProductoVariante, $this>
     */
    public function variante(): BelongsTo
    {
        return $this->belongsTo(ProductoVariante::class, 'producto_variante_id');
    }

    /**
     * @return BelongsTo<Lote, $this>
     */
    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }

    /**
     * @return BelongsTo<Ubicacion, $this>
     */
    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_id');
    }

    /**
     * @return BelongsTo<Ubicacion, $this>
     */
    public function ubicacionDetalle(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_detalle_id');
    }
}
