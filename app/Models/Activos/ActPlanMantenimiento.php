<?php

declare(strict_types=1);

namespace App\Models\Activos;

use App\Enums\Activos\EstadoPlanMantenimiento;
use App\Enums\Activos\TipoPlanMantenimiento;
use App\Models\Compras\Proveedor;
use App\Models\Monedas\Moneda;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActPlanMantenimiento extends Model
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $table = 'inv_planes_mantenimiento';

    protected $guarded = ['id'];

    protected $casts = [
        'tipo' => TipoPlanMantenimiento::class,
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
