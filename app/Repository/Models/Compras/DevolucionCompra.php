<?php

declare(strict_types=1);

namespace App\Repository\Models\Compras;

use App\Enums\Compras\EstadoDevolucion;
use App\Repository\Models\User;
use App\Traits\TieneHistorialEstado;
use Database\Factories\Compras\DevolucionCompraFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @use HasFactory<DevolucionCompraFactory>
 */
final class DevolucionCompra extends Model implements AuditableContract
{
    /** @phpstan-ignore missingType.generics */
    use Auditable, HasFactory, SoftDeletes, TieneHistorialEstado;

    protected static function newFactory(): DevolucionCompraFactory
    {
        return DevolucionCompraFactory::new();
    }

    protected $table = 'devoluciones_compra';

    protected $with = [
        'ordenCompra',
        'recepcionCompra',
        'items',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'fecha_devolucion' => 'date',
        'estado' => EstadoDevolucion::class,
    ];

    /** @return BelongsTo<OrdenCompra, $this> */
    public function ordenCompra(): BelongsTo
    {
        return $this->belongsTo(OrdenCompra::class, 'orden_compra_id');
    }

    /** @return BelongsTo<RecepcionCompra, $this> */
    public function recepcionCompra(): BelongsTo
    {
        return $this->belongsTo(RecepcionCompra::class, 'recepcion_compra_id');
    }

    /** @return BelongsTo<User, $this> */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por_id');
    }

    /** @return HasMany<DevolucionItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(DevolucionItem::class, 'devolucion_id');
    }
}
