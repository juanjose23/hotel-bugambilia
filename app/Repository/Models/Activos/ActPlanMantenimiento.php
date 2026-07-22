<?php

declare(strict_types=1);

namespace App\Repository\Models\Activos;

use App\Enums\Activos\EstadoPlanMantenimiento;
use App\Enums\Activos\TipoMantenimiento;
use App\Repository\Models\Compras\Proveedor;
use App\Repository\Models\Monedas\Moneda;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @property int $id
 * @property string $nombre
 * @property int|null $frecuencia_dias
 * @property CarbonImmutable|null $fecha_ultimo_mantenimiento
 * @property CarbonImmutable|null $fecha_proximo_mantenimiento
 * @property-read Collection<int, Activo> $activos
 */
class ActPlanMantenimiento extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'inv_planes_mantenimiento';

    protected $guarded = ['id'];

    protected $casts = [
        'tipo' => TipoMantenimiento::class,
        'estado' => EstadoPlanMantenimiento::class,
        'frecuencia_dias' => 'integer',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_ultimo_mantenimiento' => 'date',
        'fecha_proximo_mantenimiento' => 'date',
        'costo_estimado' => 'decimal:2',
    ];

    /**
     * @return BelongsTo<Proveedor, $this>
     */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    /**
     * @return BelongsTo<Moneda, $this>
     */
    public function moneda(): BelongsTo
    {
        return $this->belongsTo(Moneda::class, 'moneda_id');
    }

    /**
     * @return BelongsToMany<Activo, $this>
     */
    public function activos(): BelongsToMany
    {
        return $this->belongsToMany(Activo::class, 'act_plan_activos', 'plan_id', 'activo_id');
    }

    /**
     * @return HasMany<ActivoMantenimiento, $this>
     */
    public function mantenimientos(): HasMany
    {
        return $this->hasMany(ActivoMantenimiento::class, 'plan_id');
    }
}
