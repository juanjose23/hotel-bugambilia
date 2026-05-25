<?php

declare(strict_types=1);

namespace App\Models\Habitaciones;

use App\Enums\HabitacionesEspacios\EstadoServicioHabitacion;
use App\Models\Servicios\Servicio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class ServicioHabitacion extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'servicios_habitacion';

    protected $fillable = [
        'servicio_id',
        'habitacion_id',
        'incluido',
        'estado',
    ];

    protected $casts = [
        'incluido' => 'boolean',
        'estado' => EstadoServicioHabitacion::class,
    ];

    /** @var array<int, string> */
    protected array $auditInclude = [
        'servicio_id',
        'habitacion_id',
        'incluido',
        'estado',
    ];

    /**
     * @return BelongsTo<Servicio, $this>
     */
    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    /**
     * @return BelongsTo<Habitacion, $this>
     */
    public function habitacion(): BelongsTo
    {
        return $this->belongsTo(Habitacion::class, 'habitacion_id');
    }
}
