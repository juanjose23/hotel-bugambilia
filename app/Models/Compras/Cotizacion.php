<?php

namespace App\Models\Compras;

use App\Models\Catalogos\Catalogo;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Cotizacion extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'cotizaciones';

    protected $fillable = [
        'solicitud_id',
        'proveedor_id',
        'fecha_cotizacion',
        'fecha_vencimiento',
        'subtotal',
        'impuestos',
        'descuento',
        'costo_envio',
        'total',
        'dias_entrega',
        'moneda',
        'condicion_pago_id',
        'archivo_pdf',
        'es_elegida',
        'observaciones',
        'creada_por',
        'elegida_por',
        'elegida_en',
    ];

    protected $casts = [
        'fecha_cotizacion' => 'date',
        'fecha_vencimiento' => 'date',
        'elegida_en' => 'datetime',
        'es_elegida' => 'boolean',
        'subtotal' => 'decimal:2',
        'impuestos' => 'decimal:2',
        'descuento' => 'decimal:2',
        'costo_envio' => 'decimal:2',
        'total' => 'decimal:2',
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
}
