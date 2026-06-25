<?php

declare(strict_types=1);

namespace App\Models\Espacios;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Models\Activos\ActivoAsignacion;
use App\Models\Catalogos\Ubicacion;
use App\Models\Limpieza\LimpiezaHorarioDetalle;
use App\Models\Politicas\Politica;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Espacio extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'espacios';

    protected $guarded = ['id'];

    protected $casts = [
        'tipo' => TipoEspacio::class,
        'estado' => EstadoEspacio::class,
        'capacidad_personas' => 'integer',
        'orden' => 'integer',
        'meta_datos' => 'array',
    ];

    /** @var array<int, string> */
    protected array $auditInclude = [
        'padre_id',
        'codigo',
        'nombre',
        'descripcion',
        'tipo',
        'capacidad_personas',
        'ubicacion_id',
        'estado',
        'orden',
    ];

    /**
     * Relación con el Espacio Padre (ej. Restaurante es el padre de las mesas)
     *
     * @return BelongsTo<self, $this>
     */
    public function padre(): BelongsTo
    {
        return $this->belongsTo(self::class, 'padre_id');
    }

    /**
     * Relación con los Sub-espacios Hijos (ej. las mesas contenidas en el restaurante)
     *
     * @return HasMany<self, $this>
     */
    public function hijos(): HasMany
    {
        return $this->hasMany(self::class, 'padre_id')->orderBy('orden');
    }

    /**
     * Relación con la Ubicación física en la jerarquía general del hotel
     *
     * @return BelongsTo<Ubicacion, $this>
     */
    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_id');
    }

    /**
     * Relación con las tarifas/precios configurados para el espacio
     *
     * @return HasMany<PrecioEspacio, $this>
     */
    public function precios(): HasMany
    {
        return $this->hasMany(PrecioEspacio::class, 'espacio_id');
    }

    /**
     * Relación con los servicios asignados a este espacio
     *
     * @return HasMany<ServicioEspacio, $this>
     */
    public function serviciosEspacio(): HasMany
    {
        return $this->hasMany(ServicioEspacio::class, 'espacio_id');
    }

    /**
     * Relación polimórfica muchos a muchos con Políticas
     *
     * @return MorphToMany<Politica, $this>
     */
    public function politicas(): MorphToMany
    {
        return $this->morphToMany(Politica::class, 'politicaable')
            ->withTimestamps()
            ->wherePivotNull('deleted_at');
    }

    /**
     * @return MorphMany<ActivoAsignacion, $this>
     */
    public function asignacionesActivos(): MorphMany
    {
        return $this->morphMany(ActivoAsignacion::class, 'asignable');
    }

    /**
     * Alias semántico: activos fijos actualmente asignados a este espacio.
     *
     * @return MorphMany<ActivoAsignacion, $this>
     */
    public function inventarioFijo(): MorphMany
    {
        return $this->asignacionesActivos()->whereNull('fecha_fin');
    }

    /**
     * @return MorphMany<LimpiezaHorarioDetalle, $this>
     */
    public function horariosLimpieza(): MorphMany
    {
        return $this->morphMany(LimpiezaHorarioDetalle::class, 'limpiable');
    }

    /**
     * Relación con el stock de consumibles/blancos del espacio.
     *
     * @return HasMany<EspacioStock, $this>
     */
    public function espacioStocks(): HasMany
    {
        return $this->hasMany(EspacioStock::class, 'espacio_id');
    }

    /**
     * Obtener el nombre jerárquico completo formateado del espacio (ej: "Restaurante Bugambilias > Mesa 1")
     */
    public function getNombreCompleto(): string
    {
        // Eager-load el árbol de padres para evitar lazy-loading violations
        $this->loadMissing('padre.padre');

        $nombre = $this->nombre;
        $padre = $this->padre;

        while ($padre !== null) {
            $nombre = $padre->nombre.' > '.$nombre;
            $padre = $padre->padre;
        }

        return $nombre;
    }
}
