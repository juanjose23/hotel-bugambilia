<?php

declare(strict_types=1);

namespace App\Models\Habitaciones;

use App\Enums\HabitacionesEspacios\EstadoHabitacion;
use App\Models\Activos\ActivoAsignacion;
use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\Ubicacion;
use App\Models\Politicas\Politica;
use App\Models\Shared\Imagen;
use Illuminate\Database\Eloquent\Builder;
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
class Habitacion extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'habitaciones';

    protected $fillable = [
        'codigo',
        'numero',
        'slug',
        'nombre',
        'descripcion',
        'categoria_id',
        'ubicacion_id',
        'estado',
    ];

    protected $casts = [
        'numero' => 'integer',
        'estado' => EstadoHabitacion::class,
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
     * @return HasMany<ServicioHabitacion, $this>
     */
    public function serviciosHabitacion(): HasMany
    {
        return $this->hasMany(ServicioHabitacion::class, 'habitacion_id');
    }

    /**
     * @return HasMany<PrecioHabitacion, $this>
     */
    public function precioshabitacion(): HasMany
    {
        return $this->hasMany(PrecioHabitacion::class, 'habitacion_id');
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
        return $query->where('estado', EstadoHabitacion::Activa);
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
    public function scopeByEstado(Builder $query, EstadoHabitacion $estado): Builder
    {
        return $query->where('estado', $estado);
    }
}
