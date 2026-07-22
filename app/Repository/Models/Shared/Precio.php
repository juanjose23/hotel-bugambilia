<?php

declare(strict_types=1);

namespace App\Repository\Models\Shared;

use App\Enums\HabitacionesEspacios\TipoPrecioEspacio;
use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Monedas\Moneda;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Precio extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;
    use Auditable, SoftDeletes;

    protected $table = 'precios';

    protected $guarded = ['id'];

    protected $casts = [
        'precio' => 'decimal:2',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'estado' => EstadoGeneral::class,
        'es_oferta' => 'boolean',
        'tipo_precio' => TipoPrecioEspacio::class,
    ];

    /** @var array<int, string> */
    protected array $auditInclude = [
        'moneda_id',
        'precio',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'es_oferta',
        'tipo_precio',
    ];

    /**
     * @return MorphTo<Model, $this>
     */
    public function priceable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<Moneda, $this>
     */
    public function moneda(): BelongsTo
    {
        return $this->belongsTo(Moneda::class, 'moneda_id');
    }
}
