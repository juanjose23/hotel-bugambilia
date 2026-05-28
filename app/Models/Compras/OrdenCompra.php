<?php

namespace App\Models\Compras;

use App\Enums\Compras\EstadoOrdenCompra;
use App\Enums\Compras\EstadoRecepcion;
use App\Models\Catalogos\Catalogo;
use App\Traits\HasStatusHistory;
use Database\Factories\Compras\OrdenCompraFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @property int $id
 * @property int|null $proveedor_id
 * @property int|null $moneda_id
 * @property int|null $solicitud_id
 * @property int|null $cotizacion_id
 * @property string $codigo
 * @property bool|null $recepciones_exists
 */
class OrdenCompra extends Model implements AuditableContract
{
    /** @use HasFactory<OrdenCompraFactory> */
    use Auditable, HasFactory, HasStatusHistory, SoftDeletes;

    protected $table = 'ordenes_compra';

    protected $with = [
        'proveedor',
        'solicitud',
        'items',
        'cotizacion',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'fecha_orden' => 'date',
        'fecha_entrega_estimada' => 'date',
        'subtotal' => 'decimal:2',
        'impuestos' => 'decimal:2',
        'total' => 'decimal:2',
        'tasa_cambio' => 'decimal:4',
        'estado' => EstadoOrdenCompra::class,
    ];

    /** @return BelongsTo<Proveedor, $this> */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    /** @return BelongsTo<ProveedorContacto, $this> */
    public function contacto(): BelongsTo
    {
        return $this->belongsTo(ProveedorContacto::class, 'proveedor_contacto_id');
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

    /** @return HasMany<RecepcionCompra, $this> */
    public function recepciones(): HasMany
    {
        return $this->hasMany(RecepcionCompra::class, 'orden_compra_id');
    }

    public function isFullyReceived(): bool
    {
        if ($this->estado === EstadoOrdenCompra::Recibida) {
            return true;
        }

        $totalOrdenado = (float) $this->items()->sum('cantidad');
        if ($totalOrdenado <= 0) {
            return false;
        }

        $totalRecibido = (float) RecepcionItem::query()
            ->whereHas(
                'recepcion',
                fn (Builder $q) => $q
                    ->where('orden_compra_id', $this->id)
                    ->whereIn('estado', [
                        EstadoRecepcion::Completa,
                        EstadoRecepcion::Parcial,
                        EstadoRecepcion::ConDiscrepancia,
                        EstadoRecepcion::EnCuarentena,
                    ])
            )
            ->sum('cantidad_recibida');

        return $totalRecibido >= $totalOrdenado;
    }

    public function totalOrderedQuantity(): float
    {
        return (float) $this->items()->sum('cantidad');
    }

    public function totalReceivedQuantity(): float
    {
        return (float) RecepcionItem::query()
            ->whereHas(
                'recepcion',
                fn (Builder $q) => $q
                    ->where('orden_compra_id', $this->id)
                    ->whereIn('estado', [
                        EstadoRecepcion::Completa,
                        EstadoRecepcion::Parcial,
                        EstadoRecepcion::ConDiscrepancia,
                        EstadoRecepcion::EnCuarentena,
                    ])
            )
            ->sum('cantidad_recibida');
    }

    /** @return HasMany<OrdenCompraItem, $this> */
    public function pendingItems(): HasMany
    {
        return $this->items()->whereDoesntHave(
            'recepcionItems.recepcion',
            fn (Builder $q) => $q
                ->where('orden_compra_id', $this->id)
                ->whereIn('estado', [
                    EstadoRecepcion::Completa,
                    EstadoRecepcion::Parcial,
                    EstadoRecepcion::ConDiscrepancia,
                    EstadoRecepcion::EnCuarentena,
                ])
        );
    }

    /** @param Builder<static> $query */
    public function scopeWherePendienteRecepcion(Builder $query): void
    {
        $query->whereIn('estado', [
            EstadoOrdenCompra::Emitida,
            EstadoOrdenCompra::EnTransito,
            EstadoOrdenCompra::Parcial,
        ]);
    }
}
