<?php

declare(strict_types=1);

namespace App\Models\Shared;

use App\Enums\HabitacionesEspacios\EstadoServicioAsignacion;
use App\Models\Servicios\Servicio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class ServicioAsignacion extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'servicio_asignaciones';

    protected $guarded = ['id'];

    protected $casts = [
        'incluido' => 'boolean',
        'estado' => EstadoServicioAsignacion::class,
    ];

    /** @var array<int, string> */
    protected array $auditInclude = [
        'servicio_id',
        'incluido',
        'estado',
    ];

    /**
     * @return MorphTo<Model, $this>
     */
    public function serviceable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<Servicio, $this>
     */
    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }
}
