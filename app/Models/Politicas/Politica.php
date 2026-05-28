<?php

namespace App\Models\Politicas;

use App\Models\Habitaciones\Habitacion;
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
        'estado' => 'integer',
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
}
