<?php

declare(strict_types=1);

namespace App\Repository\Models\Espacios;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Enums\Restaurante\EstadoPedido;
use App\Repository\Models\Activos\ActivoAsignacion;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Limpieza\LimpiezaHorarioDetalle;
use App\Repository\Models\Limpieza\SolicitudLimpieza;
use App\Repository\Models\Politicas\Politica;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Shared\Imagen;
use App\Repository\Models\Shared\Precio;
use App\Repository\Models\Shared\ServicioAsignacion;
use App\Repository\Models\Shared\Stock;
use Database\Factories\Espacios\EspacioFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @use HasFactory<EspacioFactory>
 *
 * @property int|null $pedido_abierto_id
 * @property string|null $pedido_abierto_codigo
 * @property float|string|null $pedido_abierto_total
 * @property int|null $cuentas_activas_count
 * @property float|null $total_mesa
 */
final class Espacio extends Model implements AuditableContract
{
    /** @phpstan-ignore missingType.generics */
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'espacios';

    protected $guarded = ['id'];

    protected $casts = [
        'tipo' => TipoEspacio::class,
        'estado' => EstadoEspacio::class,
        'capacidad_personas' => 'integer',
        'orden' => 'integer',
        'web' => 'boolean',
        'reservable' => 'boolean',
        'meta_datos' => 'array',
    ];

    protected static function booted(): void
    {
        self::saving(function (Espacio $espacio): void {
            if (empty($espacio->slug) && ! empty($espacio->nombre)) {
                $baseSlug = Str::slug($espacio->nombre);
                $slug = $baseSlug;
                $counter = 1;
                while (static::query()->where('slug', $slug)->where('id', '!=', $espacio->id)->exists()) {
                    $slug = $baseSlug.'-'.$counter;
                    $counter++;
                }
                $espacio->slug = $slug;
            }
        });
    }

    /**
     * @param  Builder<Espacio>  $query
     * @return Builder<Espacio>
     */
    public function scopeActivosWeb($query)
    {
        return $query->where('estado', '!=', 0)->where('web', true);
    }

    /**
     * Comprueba si existe al menos un Espacio de tipo Restaurante registrado y activo en el sistema.
     */
    public static function tieneRestauranteActivo(): bool
    {
        return self::query()
            ->where('tipo', TipoEspacio::RESTAURANTE)
            ->where('estado', '!=', EstadoEspacio::Inactivo)
            ->exists();
    }

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

    /** @return BelongsTo<RecursoReservable, $this> */
    public function reservable(): BelongsTo
    {
        return $this->belongsTo(RecursoReservable::class, 'reservable_id');
    }

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

    /** @return HasMany<Pedido, $this> */
    public function pedidosActivos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'mesa_id')
            ->whereIn('estado', [
                EstadoPedido::ABIERTO,
                EstadoPedido::EN_PREPARACION,
                EstadoPedido::SERVIDO,
            ]);
    }

    /**
     * @return MorphMany<Imagen, $this>
     */
    public function imagenes(): MorphMany
    {
        return $this->morphMany(Imagen::class, 'imagenable')->orderBy('orden');
    }

    /**
     * @return MorphMany<ServicioAsignacion, $this>
     */
    public function servicioAsignaciones(): MorphMany
    {
        return $this->morphMany(ServicioAsignacion::class, 'serviceable');
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
