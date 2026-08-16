<?php

declare(strict_types=1);

namespace App\Repository\Models\Promociones;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class PromocionItem extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'promocion_items';

    protected $guarded = ['id'];

    protected $casts = [
        'precio_especial' => 'decimal:2',
    ];

    /** @return BelongsTo<Promocion, $this> */
    public function promocion(): BelongsTo
    {
        return $this->belongsTo(Promocion::class);
    }

    /** @return MorphTo<Model, $this> */
    public function item(): MorphTo
    {
        return $this->morphTo();
    }
}
