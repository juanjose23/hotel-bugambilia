<?php

declare(strict_types=1);

namespace App\Repository\Models\Habitaciones;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class DetalleHabitacion extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'detalles_habitacion';

    protected $hidden = ['vistas_legacy'];

    protected $guarded = ['id'];

    protected $casts = [
        'vistas_legacy' => 'array',
        'medidas' => 'decimal:2',
    ];

    /** @var array<int, string> */
    protected array $auditInclude = [
        'habitacion_id',
        'capacidad_adultos',
        'capacidad_ninos',
        'medidas',
    ];

    /**
     * @return BelongsTo<Habitacion, $this>
     */
    public function habitacion(): BelongsTo
    {
        return $this->belongsTo(Habitacion::class, 'habitacion_id');
    }
}
