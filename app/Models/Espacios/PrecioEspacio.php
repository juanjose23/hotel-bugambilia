<?php

declare(strict_types=1);

namespace App\Models\Espacios;

use App\Enums\Espacios\TipoPrecioEspacio;
use App\Models\Monedas\Moneda;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class PrecioEspacio extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'precios_espacio';

    protected $guarded = ['id'];

    protected $casts = [
        'precio' => 'decimal:2',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'estado' => 'integer',
        'es_oferta' => 'boolean',
        'tipo_precio' => TipoPrecioEspacio::class,
    ];

    /** @var array<int, string> */
    protected array $auditInclude = [
        'espacio_id',
        'moneda_id',
        'precio',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'es_oferta',
        'tipo_precio',
    ];

    /**
     * Relación con el Espacio Físico asociado
     *
     * @return BelongsTo<Espacio, $this>
     */
    public function espacio(): BelongsTo
    {
        return $this->belongsTo(Espacio::class, 'espacio_id');
    }

    /**
     * Relación con la Moneda asociada
     *
     * @return BelongsTo<Moneda, $this>
     */
    public function moneda(): BelongsTo
    {
        return $this->belongsTo(Moneda::class, 'moneda_id');
    }
}
