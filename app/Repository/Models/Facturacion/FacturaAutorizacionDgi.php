<?php

declare(strict_types=1);

namespace App\Repository\Models\Facturacion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

final class FacturaAutorizacionDgi extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'factura_autorizaciones_dgi';

    protected $guarded = ['id'];

    protected $attributes = [
        'activa' => true,
    ];

    protected function casts(): array
    {
        return [
            'fecha_autorizacion' => 'date',
            'vence_at' => 'date',
            'rango_desde' => 'integer',
            'rango_hasta' => 'integer',
            'activa' => 'boolean',
        ];
    }

    /** @return BelongsTo<FacturaSerie, $this> */
    public function serie(): BelongsTo
    {
        return $this->belongsTo(FacturaSerie::class, 'factura_serie_id');
    }

    /** @return HasMany<FacturaFolio, $this> */
    public function folios(): HasMany
    {
        return $this->hasMany(FacturaFolio::class, 'factura_autorizacion_dgi_id');
    }
}
