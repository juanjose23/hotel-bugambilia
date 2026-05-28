<?php

declare(strict_types=1);

namespace App\Models\Habitaciones;

use App\Enums\HabitacionesEspacios\EstadoStock;
use App\Models\Catalogos\ProductoVariante;
use App\Models\Inventario\Lote;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class HabitacionStock extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'habitacion_stocks';

    protected $guarded = ['id'];

    protected $casts = [
        'cantidad_ideal' => 'decimal:4',
        'cantidad_actual' => 'decimal:4',
        'ultima_verificacion' => 'datetime',
    ];

    protected $appends = ['estado_enum'];

    /** @return BelongsTo<Habitacion, $this> */
    public function habitacion(): BelongsTo
    {
        return $this->belongsTo(Habitacion::class, 'habitacion_id');
    }

    /** @return BelongsTo<ProductoVariante, $this> */
    public function variante(): BelongsTo
    {
        return $this->belongsTo(ProductoVariante::class, 'producto_variante_id');
    }

    /** @return BelongsTo<Lote, $this> */
    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }

    public function getEstadoEnumAttribute(): EstadoStock
    {
        return EstadoStock::calcular(
            (float) $this->cantidad_actual,
            (float) $this->cantidad_ideal
        );
    }
}
