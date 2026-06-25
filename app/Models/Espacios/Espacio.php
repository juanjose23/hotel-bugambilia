<?php

declare(strict_types=1);

namespace App\Models\Espacios;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Models\Activos\ActivoAsignacion;
use App\Models\Catalogos\Ubicacion;
use App\Models\Limpieza\LimpiezaEjecucion;
use App\Models\Limpieza\LimpiezaHorarioDetalle;
use App\Models\Limpieza\SolicitudLimpieza;
use App\Models\Politicas\Politica;
use App\Models\Shared\Precio;
use App\Models\Shared\ServicioAsignacion;
use App\Models\Shared\Stock;
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
     * @return MorphMany<Precio, $this>
     */
    public function precios(): MorphMany
    {
        return $this->morphMany(Precio::class, 'priceable');
    }

    /**
     * Relación con los servicios asignados a este espacio
     *
     * @return MorphMany<ServicioAsignacion, $this>
     */
    public function serviciosEspacio(): MorphMany
    {
        return $this->morphMany(ServicioAsignacion::class, 'serviceable');
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
     * Obtener el nombre jerárquico completo formateado del espacio (ej: "Restaurante Bugambilias > Mesa 1")
     *
     * Requires `padre.padre` to be eager-loaded before calling in a loop to avoid N+1.
     */
    public function getNombreCompleto(): string
    {
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
