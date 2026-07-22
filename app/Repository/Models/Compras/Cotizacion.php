<?php

declare(strict_types=1);

namespace App\Repository\Models\Compras;

use App\Enums\Compras\EstadoCotizacion;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @property int $items_elegidos_count
 */
final class Cotizacion extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'cotizaciones';

    protected $with = [
        'proveedor',
        'items',
        'moneda',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'fecha_cotizacion' => 'date',
        'fecha_vencimiento' => 'date',
        'elegida_en' => 'datetime',
        'es_elegida' => 'boolean',
        'estado' => EstadoCotizacion::class,
        'subtotal' => 'decimal:2',
        'impuestos' => 'decimal:2',
        'descuento' => 'decimal:2',
        'costo_envio' => 'decimal:2',
        'total' => 'decimal:2',
        'tasa_cambio' => 'decimal:4',
    ];

    /** @return BelongsTo<Solicitud, $this> */
    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class, 'solicitud_id');
    }

    /** @return BelongsTo<Proveedor, $this> */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    /** @return BelongsTo<Catalogo, $this> */
    public function condicionPago(): BelongsTo
    {
        return $this->belongsTo(Catalogo::class, 'condicion_pago_id');
    }

    /** @return HasMany<CotizacionItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(CotizacionItem::class, 'cotizacion_id');
    }

    /** @return BelongsTo<User, $this> */
    public function creadaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creada_por');
    }

    /** @return BelongsTo<User, $this> */
    public function elegidaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'elegida_por');
    }

    /** @return HasOne<OrdenCompra, $this> */
    public function ordenCompra(): HasOne
    {
        return $this->hasOne(OrdenCompra::class, 'cotizacion_id');
    }

    /** @return BelongsTo<Moneda, $this> */
    public function moneda(): BelongsTo
    {
        return $this->belongsTo(Moneda::class, 'moneda_id');
    }
}
