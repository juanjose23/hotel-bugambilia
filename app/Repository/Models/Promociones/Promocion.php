<?php

declare(strict_types=1);

namespace App\Repository\Models\Promociones;

use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Politicas\Politica;
use App\Repository\Models\Shared\Imagen;
use App\Repository\Models\Shared\Precio;
use App\Repository\Models\Shared\Stock;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Promocion extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'promociones';

    protected $guarded = ['id'];

    protected $casts = [
        'estado' => EstadoGeneral::class,
        'web' => 'boolean',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'precio_paquete' => 'decimal:2',
        'descuento_porcentaje' => 'decimal:2',
        'descuento_monto' => 'decimal:2',
    ];

    /**
     * @return MorphMany<Precio, $this>
     */
    public function precios(): MorphMany
    {
        return $this->morphMany(Precio::class, 'priceable');
    }

    /**
     * Calcula el precio final del paquete aplicando los descuentos configurados.
     */
    public function getPrecioFinalAttribute(): ?float
    {
        $precioBase = $this->precio_paquete !== null ? (float) $this->precio_paquete : null;

        if ($precioBase === null) {
            $precioObj = $this->precios->first();
            if ($precioObj !== null && $precioObj->precio > 0) {
                $precioBase = (float) $precioObj->precio;
            }
        }

        if ($precioBase === null) {
            return null;
        }

        $precio = $precioBase;

        if ($this->descuento_porcentaje) {
            $precio -= ($precio * ((float) $this->descuento_porcentaje / 100));
        } elseif ($this->descuento_monto) {
            $precio -= (float) $this->descuento_monto;
        }

        return max(0.0, round($precio, 2));
    }

    /** @return BelongsTo<Catalogo, $this> */
    public function tipo(): BelongsTo
    {
        return $this->belongsTo(Catalogo::class, 'tipo_promocion_id');
    }

    /** @return MorphMany<Imagen, $this> */
    public function imagenes(): MorphMany
    {
        return $this->morphMany(Imagen::class, 'imagenable');
    }

    /** @return HasMany<PromocionItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(PromocionItem::class, 'promocion_id');
    }

    /** @return MorphToMany<Politica, $this> */
    public function politicas(): MorphToMany
    {
        return $this->morphToMany(Politica::class, 'politicaable')
            ->withTimestamps()
            ->wherePivotNull('deleted_at');
    }

    /** @return MorphMany<Stock, $this> */
    public function stocks(): MorphMany
    {
        return $this->morphMany(Stock::class, 'stockable');
    }

    /** @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado', EstadoGeneral::Activo->value);
    }

    /** @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeVigentes(Builder $query): Builder
    {
        return $query->activos()
            ->where('fecha_inicio', '<=', now()->toDateString())
            ->where('fecha_fin', '>=', now()->toDateString());
    }
}
