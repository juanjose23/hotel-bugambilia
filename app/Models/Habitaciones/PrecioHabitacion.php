<?php

declare(strict_types=1);

namespace App\Models\Habitaciones;

use App\Models\Monedas\Moneda;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class PrecioHabitacion extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'precios_habitacion';

    protected $fillable = [
        'habitacion_id',
        'moneda_id',
        'precio',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'es_oferta',
    ];

    protected $casts = [
        'moneda_id' => 'integer',
        'precio' => 'decimal:2',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'estado' => 'integer',
        'es_oferta' => 'boolean',
    ];

    /**
     * @return BelongsTo<Habitacion, $this>
     */
    public function habitacion(): BelongsTo
    {
        return $this->belongsTo(Habitacion::class, 'habitacion_id');
    }

    /**
     * @return BelongsTo<Moneda, $this>
     */
    public function moneda(): BelongsTo
    {
        return $this->belongsTo(Moneda::class);
    }
}
