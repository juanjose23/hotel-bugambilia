<?php

declare(strict_types=1);

namespace App\Models\Limpieza;

use App\Enums\HabitacionesEspacios\EstadoLimpieza;
use App\Models\Catalogos\Ubicacion;
use App\Models\Colaboradores\Colaborador;
use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\Habitacion;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @property int $id
 * @property int|null $horario_id
 * @property int|null $solicitud_id
 * @property string $limpiable_type
 * @property int $limpiable_id
 * @property int $turno_id
 * @property int|null $colaborador_id
 * @property int|null $carrito_id
 * @property Carbon $fecha
 * @property string|null $hora_inicio
 * @property string|null $hora_fin
 * @property EstadoLimpieza $estado
 * @property array<string, bool>|null $detalles_checklist
 * @property string|null $observaciones
 * @property Carbon|null $recordatorio_enviado_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read LimpiezaHorario|null $horario
 * @property-read SolicitudLimpieza|null $solicitud
 * @property-read Model|Habitacion|Espacio|Ubicacion|null $limpiable
 * @property-read Turno $turno
 * @property-read Colaborador|null $colaborador
 */
class LimpiezaEjecucion extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'limp_ejecuciones';

    protected $guarded = ['id'];

    protected $casts = [
        'horario_id' => 'integer',
        'solicitud_id' => 'integer',
        'limpiable_id' => 'integer',
        'turno_id' => 'integer',
        'colaborador_id' => 'integer',
        'carrito_id' => 'integer',
        'fecha' => 'date',
        'detalles_checklist' => 'array',
        'estado' => EstadoLimpieza::class,
        'hora_inicio' => 'string',
        'hora_fin' => 'string',
        'recordatorio_enviado_at' => 'datetime',
        'estado_previo' => 'integer',
        'consumos' => 'array',
    ];

    /**
     * @return BelongsTo<LimpiezaHorario, $this>
     */
    public function horario(): BelongsTo
    {
        return $this->belongsTo(LimpiezaHorario::class, 'horario_id');
    }

    /**
     * @return BelongsTo<SolicitudLimpieza, $this>
     */
    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(SolicitudLimpieza::class, 'solicitud_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function limpiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<Turno, $this>
     */
    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class, 'turno_id');
    }

    /**
     * @return BelongsTo<Colaborador, $this>
     */
    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class, 'colaborador_id');
    }

    /**
     * @return BelongsTo<Ubicacion, $this>
     */
    public function carrito(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'carrito_id');
    }
}
