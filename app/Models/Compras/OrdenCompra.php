<?php

namespace App\Models\Compras;

use App\Enums\Compras\EstadoOrdenCompra;
use App\Models\Catalogos\Catalogo;
use App\Traits\HasStatusHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @property int $id
 * @property int|null $proveedor_id
 * @property int|null $solicitud_id
 * @property int|null $cotizacion_id
 * @property string $codigo
 */
class OrdenCompra extends Model implements AuditableContract
{
    use Auditable, HasStatusHistory, SoftDeletes;

    protected $table = 'ordenes_compra';

    protected $fillable = [
        'codigo',
        'proveedor_id',
        'solicitud_id',
        'cotizacion_id',
        'fecha_orden',
        'fecha_entrega_estimada',
        'condicion_pago_id',
        'subtotal',
        'impuestos',
        'total',
        'estado',
        'notas',
    ];

    protected $casts = [
        'fecha_orden' => 'date',
        'fecha_entrega_estimada' => 'date',
        'subtotal' => 'decimal:2',
        'impuestos' => 'decimal:2',
        'total' => 'decimal:2',
        'estado' => EstadoOrdenCompra::class,
    ];

    /** @return BelongsTo<Proveedor, $this> */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    /** @return BelongsTo<Solicitud, $this> */
    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class);
    }

    /** @return BelongsTo<Cotizacion, $this> */
    public function cotizacion(): BelongsTo
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id');
    }

    /** @return BelongsTo<Catalogo, $this> */
    public function condicionPago(): BelongsTo
    {
        return $this->belongsTo(Catalogo::class, 'condicion_pago_id');
    }

    /** @return HasMany<OrdenCompraItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(OrdenCompraItem::class, 'orden_compra_id');
    }
}
