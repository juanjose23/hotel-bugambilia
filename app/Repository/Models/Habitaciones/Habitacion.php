<?php

declare(strict_types=1);

namespace App\Repository\Models\Habitaciones;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Repository\Models\Activos\ActivoAsignacion;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Limpieza\LimpiezaHorarioDetalle;
use App\Repository\Models\Limpieza\SolicitudLimpieza;
use App\Repository\Models\Politicas\Politica;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Shared\Imagen;
use App\Repository\Models\Shared\Precio;
use App\Repository\Models\Shared\ServicioAsignacion;
use App\Repository\Models\Shared\Stock;
use Database\Factories\Habitaciones\HabitacionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @property string|null $nombre
 */
/**
 * @use HasFactory<HabitacionFactory>
 */
class Habitacion extends Model implements AuditableContract
{
    /** @phpstan-ignore missingType.generics */
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'habitaciones';

    protected $guarded = ['id'];

    protected $casts = [
        'numero' => 'integer',
        'estado' => EstadoEspacio::class,
    ];

    /** @var array<int, string> */
    protected array $auditInclude = [
        'codigo',
        'numero',
        'slug',
        'nombre',
        'descripcion',
        'categoria_id',
        'ubicacion_id',
        'estado',
    ];

    /**
     * @return BelongsTo<Catalogo, $this>
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Catalogo::class, 'categoria_id');
    }

    /**
     * @return BelongsTo<Ubicacion, $this>
     */
    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_id');
    }

    /** @return BelongsTo<RecursoReservable, $this> */
    public function reservable(): BelongsTo
    {
        return $this->belongsTo(RecursoReservable::class, 'reservable_id');
    }

    /**
     * @return HasOne<DetalleHabitacion, $this>
     */
    public function detalle(): HasOne
    {
        return $this->hasOne(DetalleHabitacion::class, 'habitacion_id');
    }

    /**
     * @return MorphToMany<Politica, $this>
     */
    public function politicas(): MorphToMany
    {
        return $this->morphToMany(Politica::class, 'politicaable')
            ->withTimestamps()
            ->wherePivotNull('deleted_at');
    }

    /**
     * @return MorphMany<Imagen, $this>
     */
    public function imagenes(): MorphMany
    {
        return $this->morphMany(Imagen::class, 'imagenable');
    }

    /**
     * @return MorphMany<ActivoAsignacion, $this>
     */
    public function asignacionesActivos(): MorphMany
    {
        return $this->morphMany(ActivoAsignacion::class, 'asignable');
    }

    /**
     * Alias semántico: activos fijos actualmente asignados a esta habitación.
     *
     * @return MorphMany<ActivoAsignacion, $this>
     */
    public function inventarioFijo(): MorphMany
    {
        return $this->asignacionesActivos()->whereNull('fecha_fin');
    }

    /**
     * @return MorphMany<ServicioAsignacion, $this>
     */
    public function servicioAsignaciones(): MorphMany
    {
        return $this->morphMany(ServicioAsignacion::class, 'serviceable');
    }

    /**
     * @return MorphMany<Precio, $this>
     */
    public function precios(): MorphMany
    {
        return $this->morphMany(Precio::class, 'priceable');
    }

    /**
     * @return MorphMany<Stock, $this>
     */
    public function stocks(): MorphMany
    {
        return $this->morphMany(Stock::class, 'stockable');
    }

    /**
     * @return MorphMany<SolicitudLimpieza, $this>
     */
    public function solicitudesLimpieza(): MorphMany
    {
        return $this->morphMany(SolicitudLimpieza::class, 'limpiable');
    }

    /**
     * @return MorphMany<LimpiezaHorarioDetalle, $this>
     */
    public function horariosLimpieza(): MorphMany
    {
        return $this->morphMany(LimpiezaHorarioDetalle::class, 'limpiable');
    }

    /**
     * @return MorphMany<LimpiezaEjecucion, $this>
     */
    public function ejecucionesLimpieza(): MorphMany
    {
        return $this->morphMany(LimpiezaEjecucion::class, 'limpiable');
    }

    /**
     * @return HasMany<HabitacionHistorial, $this>
     */
    public function historial(): HasMany
    {
        return $this->hasMany(HabitacionHistorial::class, 'model_id')
            ->where('model_type', static::class);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('estado', EstadoEspacio::Activa);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeByCategoria(Builder $query, int $categoriaId): Builder
    {
        return $query->where('categoria_id', $categoriaId);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeByUbicacion(Builder $query, int $ubicacionId): Builder
    {
        return $query->where('ubicacion_id', $ubicacionId);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeByEstado(Builder $query, EstadoEspacio $estado): Builder
    {
        return $query->where('estado', $estado);
    }
}
