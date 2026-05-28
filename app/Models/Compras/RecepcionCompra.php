<?php

namespace App\Models\Compras;

use App\Enums\Compras\EstadoRecepcion;
use App\Models\Catalogos\Ubicacion;
use App\Models\User;
use App\Traits\HasStatusHistory;
use Database\Factories\Compras\RecepcionCompraFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class RecepcionCompra extends Model implements AuditableContract
{
    /** @use HasFactory<RecepcionCompraFactory> */
    use Auditable, HasFactory, HasStatusHistory, SoftDeletes;

    protected $table = 'recepciones_compra';

    protected $with = [
        'ordenCompra',
        'items',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'fecha_recepcion' => 'date',
        'estado' => EstadoRecepcion::class,
    ];

    /** @return BelongsTo<Ubicacion, $this> */
    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_id');
    }

    /** @return BelongsTo<OrdenCompra, $this> */
    public function ordenCompra(): BelongsTo
    {
        return $this->belongsTo(OrdenCompra::class, 'orden_compra_id');
    }

    /** @return BelongsTo<User, $this> */
    public function receptor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recibido_por_id');
    }

    /** @return HasMany<RecepcionItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(RecepcionItem::class, 'recepcion_id');
    }
}
