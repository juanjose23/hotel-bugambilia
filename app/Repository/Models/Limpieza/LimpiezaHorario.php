<?php

declare(strict_types=1);

namespace App\Repository\Models\Limpieza;

use App\Enums\Shared\EstadoGeneral;
use Database\Factories\Limpieza\LimpiezaHorarioFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @use HasFactory<LimpiezaHorarioFactory>
 */
class LimpiezaHorario extends Model implements AuditableContract
{
    /** @phpstan-ignore missingType.generics */
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'limp_horarios';

    protected $guarded = ['id'];

    protected $casts = [
        'turno_id' => 'integer',
        'activo' => EstadoGeneral::class,
        'checklist' => 'array',
    ];

    /** @return HasMany<LimpiezaHorarioDetalle, $this> */
    public function detalles(): HasMany
    {
        return $this->hasMany(LimpiezaHorarioDetalle::class, 'horario_id');
    }

    /** @return BelongsTo<Turno, $this> */
    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class, 'turno_id');
    }

    /** @return HasMany<LimpiezaEjecucion, $this> */
    public function ejecuciones(): HasMany
    {
        return $this->hasMany(LimpiezaEjecucion::class, 'horario_id');
    }
}
