<?php

namespace App\Repository\Models\Politicas;

use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Promociones\Promocion;
use App\Repository\Models\Servicios\Servicio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Politica extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'politicas';

    protected $guarded = ['id'];

    protected $casts = [
        'estado' => EstadoGeneral::class,
    ];

    /**
     * @return MorphToMany<Habitacion, $this>
     */
    public function habitaciones(): MorphToMany
    {
        return $this->morphedByMany(Habitacion::class, 'politicaable')
            ->withTimestamps()
            ->wherePivotNull('deleted_at');
    }

    /**
     * @return MorphToMany<Servicio, $this>
     */
    public function servicios(): MorphToMany
    {
        return $this->morphedByMany(Servicio::class, 'politicaable')
            ->withTimestamps()
            ->wherePivotNull('deleted_at');
    }

    /**
     * @return MorphToMany<Promocion, $this>
     */
    public function promociones(): MorphToMany
    {
        return $this->morphedByMany(Promocion::class, 'politicaable')
            ->withTimestamps()
            ->wherePivotNull('deleted_at');
    }
}
