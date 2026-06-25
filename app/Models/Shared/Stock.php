<?php

declare(strict_types=1);

namespace App\Models\Shared;

use App\Models\Catalogos\ProductoVariante;
use App\Models\Inventario\Lote;
use App\Traits\HasStockStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Stock extends Model implements AuditableContract
{
    use Auditable, HasStockStatus, SoftDeletes;

    protected $table = 'stocks';

    protected $guarded = ['id'];

    protected $casts = [
        'cantidad_ideal' => 'decimal:4',
        'cantidad_actual' => 'decimal:4',
        'ultima_verificacion' => 'datetime',
    ];

    /**
     * @return MorphTo<Model, $this>
     */
    public function stockable(): MorphTo
    {
        return $this->morphTo();
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
}
