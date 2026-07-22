<?php

declare(strict_types=1);

namespace App\Repository\Models\Limpieza;

use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Colaboradores\Colaborador;
use Database\Factories\Limpieza\LimpiezaEjecucionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/** @use HasFactory<LimpiezaEjecucionFactory> */
class LimpiezaEjecucion extends Model implements AuditableContract
{
    /** @phpstan-ignore missingType.generics */
    use Auditable, HasFactory, SoftDeletes;

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

    /** @return BelongsTo<LimpiezaHorario, $this> */
    public function horario(): BelongsTo
    {
        return $this->belongsTo(LimpiezaHorario::class, 'horario_id');
    }

    /** @return BelongsTo<SolicitudLimpieza, $this> */
    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(SolicitudLimpieza::class, 'solicitud_id');
    }

    /** @return MorphTo<Model, $this> */
    public function limpiable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<Turno, $this> */
    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class, 'turno_id');
    }

    /** @return BelongsTo<Colaborador, $this> */
    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class, 'colaborador_id');
    }

    /** @return BelongsTo<Ubicacion, $this> */
    public function carrito(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'carrito_id');
    }
}
