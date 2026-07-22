<?php

declare(strict_types=1);

namespace App\Repository\Models\Restaurante;

use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Personas\Persona;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Pedido extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'pedidos';

    protected $guarded = ['id'];

    protected $casts = [
        'total' => 'decimal:2',
        'abierto_en' => 'datetime',
        'cerrado_en' => 'datetime',
    ];

    /** @return BelongsTo<Espacio, $this> */
    public function mesa(): BelongsTo
    {
        return $this->belongsTo(Espacio::class, 'mesa_id');
    }

    /** @return BelongsTo<Colaborador, $this> */
    public function mesero(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class, 'mesero_id');
    }

    /** @return BelongsTo<Persona, $this> */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'cliente_id');
    }

    /** @return HasMany<PedidoItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(PedidoItem::class, 'pedido_id');
    }
}
