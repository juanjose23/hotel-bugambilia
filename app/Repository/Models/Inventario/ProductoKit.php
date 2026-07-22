<?php

declare(strict_types=1);

namespace App\Repository\Models\Inventario;

use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class ProductoKit extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;
    use Auditable, SoftDeletes;

    protected $table = 'producto_kit';

    protected $guarded = ['id'];

    protected $casts = [
        'cantidad' => 'decimal:4',
    ];

    /** @return BelongsTo<Producto, $this> */
    public function productoPadre(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_padre_id');
    }

    /** @return BelongsTo<ProductoVariante, $this> */
    public function variante(): BelongsTo
    {
        return $this->belongsTo(ProductoVariante::class, 'producto_variante_id');
    }

    /** @return BelongsTo<Lote, $this> */
    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }
}
