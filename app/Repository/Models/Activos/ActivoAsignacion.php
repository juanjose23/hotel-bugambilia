<?php

declare(strict_types=1);

namespace App\Repository\Models\Activos;

use App\Enums\Activos\EstadoAsignacion;
use App\Filament\Shared\Concerns\TipoDestinoAsignacionActivo;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class ActivoAsignacion extends Model implements Auditable
{
    use AuditableTrait, SoftDeletes;

    protected $table = 'inv_activo_asignaciones';

    protected $guarded = ['id'];

    protected $casts = [
        'estado' => EstadoAsignacion::class,
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    /**
     * @return BelongsTo<Activo, $this>
     */
    public function activo(): BelongsTo
    {
        return $this->belongsTo(Activo::class, 'activo_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function asignable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function asignadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_por_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function recibidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recibido_por_id');
    }

    public function tipoDestinoLabel(): string
    {
        return TipoDestinoAsignacionActivo::tipoDestinoLabel($this->asignable_type);
    }

    public function tipoDestinoColor(): string
    {
        return TipoDestinoAsignacionActivo::tipoDestinoColor($this->asignable_type);
    }

    public function destinoLabel(): string
    {
        $asignable = $this->asignable;

        if ($asignable instanceof Habitacion || $asignable instanceof Ubicacion || $asignable instanceof Espacio || $asignable instanceof Servicio) {
            return (string) ($asignable->nombre ?? '');
        }

        return 'Sin asignar';
    }
}
