<?php

namespace App\Models\Catalogos;

use App\Models\Activos\ActivoAsignacion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
     * @return BelongsTo<self, $this>
     */
    public function padre(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'padre_id');
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
}
