<?php

declare(strict_types=1);

namespace App\Repository\Models\Restaurante;

use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

final class ProcesoCocina extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'procesos_cocina';

    protected $guarded = ['id'];

    protected $attributes = ['tipo_registro' => 'borrador'];

    protected static function booted(): void
    {
        self::updating(function (self $proceso): void {
            if ($proceso->getRawOriginal('confirmado_at') !== null || $proceso->getRawOriginal('tipo_registro') !== 'borrador') {
                throw new \DomainException('El registro confirmado, histórico o de pedido no se puede modificar.');
            }
        });
        self::deleting(function (self $proceso): void {
            if ($proceso->getAttribute('confirmado_at') !== null || $proceso->getAttribute('tipo_registro') !== 'borrador') {
                throw new \DomainException('El registro confirmado, histórico o de pedido no se puede eliminar.');
            }
        });
    }

    protected $casts = [
        'cantidad_procesada' => 'decimal:3',
        'cantidad_platos' => 'integer',
        'costo_total' => 'decimal:2',
    ];

    /** @return BelongsTo<Plato, $this> */
    public function plato(): BelongsTo
    {
        return $this->belongsTo(Plato::class, 'plato_id');
    }

    /** @return BelongsTo<Producto, $this> */
    public function productoOrigen(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_origen_id');
    }

    /** @return BelongsTo<ProductoVariante, $this> */
    public function varianteOrigen(): BelongsTo
    {
        return $this->belongsTo(ProductoVariante::class, 'variante_origen_id');
    }

    /** @return BelongsTo<User, $this> */
    public function realizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'realizado_por');
    }

    /** @return BelongsTo<Lote, $this> */
    public function loteResultado(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'lote_resultado_id');
    }

    /** @return HasMany<ProcesoItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(ProcesoItem::class, 'proceso_id');
    }

    public function getCostoPorPlatoAttribute(): float
    {
        $cant = (int) $this->cantidad_platos;
        if ($cant <= 0) {
            return 0.0;
        }

        return round(((float) $this->costo_total) / $cant, 2);
    }
}
