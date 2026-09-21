<?php

declare(strict_types=1);

namespace App\Repository\Models\Servicios;

use App\Repository\Models\Activos\ActivoAsignacion;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Politicas\Politica;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Shared\Imagen;
use App\Repository\Models\Shared\Precio;
use App\Repository\Models\Shared\ServicioAsignacion;
use App\Repository\Models\Shared\Stock;
use App\Repository\Queries\Servicios\ObtenerListadoHeroicons;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Servicio extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $guarded = ['id'];

    /** @var array<int, string> */
    protected array $dates = ['created_at', 'updated_at', 'deleted_at'];

    protected $casts = [
        'estado' => 'integer',
        'web' => 'boolean',
    ];

    protected $table = 'servicios';

    /** @return BelongsTo<Catalogo, $this> */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Catalogo::class, 'categoria_id');
    }

    /** @return BelongsTo<RecursoReservable, $this> */
    public function reservable(): BelongsTo
    {
        return $this->belongsTo(RecursoReservable::class, 'reservable_id');
    }

    /** @return HasMany<ServicioAsignacion, $this> */
    public function servicioAsignaciones(): HasMany
    {
        return $this->hasMany(ServicioAsignacion::class, 'servicio_id');
    }

    /** @return MorphMany<Precio, $this> */
    public function precios(): MorphMany
    {
        return $this->morphMany(Precio::class, 'priceable');
    }

    /** @return MorphMany<Imagen, $this> */
    public function imagenes(): MorphMany
    {
        return $this->morphMany(Imagen::class, 'imagenable');
    }

    /** @return MorphMany<Stock, $this> */
    public function stocks(): MorphMany
    {
        return $this->morphMany(Stock::class, 'stockable');
    }

    /**
     * Activos fijos asignados a este servicio (flota).
     *
     * @return MorphMany<ActivoAsignacion, $this>
     */
    public function asignacionesActivos(): MorphMany
    {
        return $this->morphMany(ActivoAsignacion::class, 'asignable');
    }

    /**
     * Alias semántico: activos fijos actualmente asignados a este servicio.
     *
     * @return MorphMany<ActivoAsignacion, $this>
     */
    public function inventarioFijo(): MorphMany
    {
        return $this->asignacionesActivos()->whereNull('fecha_fin');
    }

    /** @return MorphToMany<Politica, $this> */
    public function politicas(): MorphToMany
    {
        return $this->morphToMany(Politica::class, 'politicaable')
            ->withTimestamps()
            ->wherePivotNull('deleted_at');
    }

    /**
     * Resuelve el identificador de icono para componentes de Filament.
     */
    public function getIconoFilamentAttribute(): string
    {
        return ObtenerListadoHeroicons::resolverParaFilament($this->icono);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado', 1);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActivosWeb(Builder $query): Builder
    {
        return $query->where('estado', 1)->where('web', true);
    }
}
