<?php

namespace App\Models\Catalogos;

use App\Models\Activos\ActivoAsignacion;
use App\Models\Limpieza\LimpiezaEjecucion;
use App\Models\Limpieza\LimpiezaHorarioDetalle;
use App\Models\Limpieza\SolicitudLimpieza;
use App\Models\Shared\Stock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Ubicacion extends Model implements AuditableContract
{
    //
    use Auditable, SoftDeletes;

    protected $table = 'ubicaciones';

    protected $guarded = ['id'];

    /**
     * Obtiene los IDs de todas las ubicaciones descendientes, incluyendo esta misma.
     *
     * @return array<int>
     */
    public static function obtenerDescendientesIds(int $id): array
    {
        $descendants = [$id];
        $toProcess = [$id];

        while ($toProcess) {
            $children = self::whereIn('padre_id', $toProcess)->pluck('id')->toArray();
            if (empty($children)) {
                break;
            }
            $descendants = array_merge($descendants, $children);
            $toProcess = $children;
        }

        return array_unique($descendants);
    }

    /**
     * @return BelongsTo<self, $this>
     */
    public function padre(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'padre_id');
    }

    /**
     * @return HasMany<self, $this>
     */
    public function hijos(): HasMany
    {
        return $this->hasMany(Ubicacion::class, 'padre_id');
    }

    /**
     * @return MorphMany<ActivoAsignacion, $this>
     */
    public function asignacionesActivos(): MorphMany
    {
        return $this->morphMany(ActivoAsignacion::class, 'asignable');
    }

    /**
     * Alias semántico: activos fijos actualmente asignados a esta ubicación/bodega.
     *
     * @return MorphMany<ActivoAsignacion, $this>
     */
    public function inventarioFijo(): MorphMany
    {
        return $this->asignacionesActivos()->whereNull('fecha_fin');
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
     * @return MorphMany<Stock, $this>
     */
    public function stocks(): MorphMany
    {
        return $this->morphMany(Stock::class, 'stockable');
    }
}
