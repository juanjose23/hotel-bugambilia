<?php

declare(strict_types=1);

namespace App\Repository\Models\Facturacion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

final class FacturaSerie extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'factura_series';

    protected $guarded = ['id'];

    protected $attributes = [
        'activa' => true,
        'siguiente_numero' => 1,
    ];

    protected function casts(): array
    {
        return [
            'activa' => 'boolean',
            'siguiente_numero' => 'integer',
        ];
    }

    /** @return HasMany<FacturaAutorizacionDgi, $this> */
    public function autorizaciones(): HasMany
    {
        return $this->hasMany(FacturaAutorizacionDgi::class);
    }

    /** @return HasMany<Factura, $this> */
    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class);
    }

    /** @return HasMany<FacturaFolio, $this> */
    public function folios(): HasMany
    {
        return $this->hasMany(FacturaFolio::class);
    }
}
