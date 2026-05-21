<?php

namespace App\Models\Compras;

use App\Enums\Compras\EstadoDevolucion;
use App\Models\User;
use App\Traits\HasStatusHistory;
use Database\Factories\Compras\DevolucionCompraFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class DevolucionCompra extends Model implements AuditableContract
{
    /** @use HasFactory<DevolucionCompraFactory> */
    use Auditable, HasFactory, HasStatusHistory, SoftDeletes;

    protected $table = 'devoluciones_compra';

    protected $with = [
        'ordenCompra',
        'recepcionCompra',
        'items',
    ];

    protected $fillable = [
        'codigo',
        'orden_compra_id',
        'recepcion_compra_id',
        'fecha_devolucion',
        'estado',
        'motivo',
        'documento_externo',
        'creado_por_id',
    ];

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
