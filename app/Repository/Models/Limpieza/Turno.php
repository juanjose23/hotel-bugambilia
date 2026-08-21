<?php

declare(strict_types=1);

namespace App\Repository\Models\Limpieza;

use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Colaboradores\Colaborador;
use Database\Factories\Limpieza\TurnoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/** @use HasFactory<TurnoFactory> */
class Turno extends Model implements AuditableContract
{
    /** @phpstan-ignore missingType.generics */
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'limp_horario_turnos';

    protected $guarded = ['id'];

    protected $with = [
        'lider.persona',
        'apoyo.persona',
        'carritos',
    ];

    protected $casts = [
        'lider_id' => 'integer',
        'apoyo_id' => 'integer',
        'es_lavanderia' => 'boolean',
        'estado' => EstadoGeneral::class,
    ];

    /** @return BelongsTo<Colaborador, $this> */
    public function lider(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class, 'lider_id');
    }

    /** @return BelongsTo<Colaborador, $this> */
    public function apoyo(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class, 'apoyo_id');
    }

    /** @return BelongsToMany<Ubicacion, $this> */
    public function carritos(): BelongsToMany
    {
        return $this->belongsToMany(Ubicacion::class, 'limp_turno_carritos', 'turno_id', 'ubicacion_id');
    }

    /** @return HasMany<LimpiezaHorario, $this> */
    public function horarios(): HasMany
    {
        return $this->hasMany(LimpiezaHorario::class, 'turno_id');
    }

    /** @return HasMany<LimpiezaEjecucion, $this> */
    public function ejecuciones(): HasMany
    {
        return $this->hasMany(LimpiezaEjecucion::class, 'turno_id');
    }
}
