<?php

declare(strict_types=1);

namespace App\Repository\Models\Promociones;

use App\Enums\Promociones\TipoBeneficioCliente;
use App\Repository\Models\Catalogos\Catalogo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class PromocionBeneficio extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'promocion_beneficios';

    protected $guarded = ['id'];

    protected $casts = [
        'tipo' => TipoBeneficioCliente::class,
        'valor' => 'decimal:2',
        'es_porcentaje' => 'boolean',
        'combinable' => 'boolean',
        'activo' => 'boolean',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'metadata' => 'array',
    ];

    /** @return BelongsTo<Promocion, $this> */
    public function promocion(): BelongsTo
    {
        return $this->belongsTo(Promocion::class);
    }

    /** @return BelongsTo<Catalogo, $this> */
    public function segmentoCliente(): BelongsTo
    {
        return $this->belongsTo(Catalogo::class, 'segmento_cliente_id');
    }

    /** @return HasMany<PromocionBeneficioRegla, $this> */
    public function reglas(): HasMany
    {
        return $this->hasMany(PromocionBeneficioRegla::class, 'beneficio_id');
    }

    /** @return HasMany<PromocionBeneficioUso, $this> */
    public function usos(): HasMany
    {
        return $this->hasMany(PromocionBeneficioUso::class, 'beneficio_id');
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeVigentes(Builder $query): Builder
    {
        $hoy = now()->toDateString();

        return $query->activos()
            ->where(fn (Builder $query) => $query->whereNull('fecha_inicio')->orWhere('fecha_inicio', '<=', $hoy))
            ->where(fn (Builder $query) => $query->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', $hoy));
    }
}
