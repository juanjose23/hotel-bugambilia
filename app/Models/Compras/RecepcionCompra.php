<?php

namespace App\Models\Compras;

use App\Enums\Compras\EstadoRecepcion;
use App\Models\User;
use App\Traits\HasStatusHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class RecepcionCompra extends Model implements AuditableContract
{
    use Auditable, HasStatusHistory, SoftDeletes;

    protected $table = 'recepciones_compra';

    protected $fillable = [
        'codigo',
        'orden_compra_id',
        'fecha_recepcion',
        'guia_remision',
        'recibido_por_id',
        'estado',
        'notas',
    ];

    protected $casts = [
        'fecha_recepcion' => 'date',
        'estado' => EstadoRecepcion::class,
    ];

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
