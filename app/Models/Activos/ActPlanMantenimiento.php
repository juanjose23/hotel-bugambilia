<?php

declare(strict_types=1);

namespace App\Models\Activos;

use App\Models\Compras\Proveedor;
use App\Models\Monedas\Moneda;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActPlanMantenimiento extends Model
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $table = 'inv_planes_mantenimiento';

    protected $guarded = ['id'];

    protected $casts = [
        'frecuencia_dias' => 'integer',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
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
     * @return HasMany<ActivoMantenimiento, $this>
     */
    public function mantenimientos(): HasMany
    {
        return $this->hasMany(ActivoMantenimiento::class, 'plan_id');
    }
}
