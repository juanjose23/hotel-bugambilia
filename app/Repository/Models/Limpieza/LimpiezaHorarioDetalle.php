<?php

declare(strict_types=1);

namespace App\Repository\Models\Limpieza;

use Database\Factories\Limpieza\LimpiezaHorarioDetalleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/** @use HasFactory<LimpiezaHorarioDetalleFactory> */
class LimpiezaHorarioDetalle extends Model implements AuditableContract
{
    /** @phpstan-ignore missingType.generics */
    use Auditable, HasFactory;

    protected $table = 'limp_horario_detalles';

    protected $guarded = ['id'];

    protected $with = ['limpiable'];

    protected $casts = [
        'horario_id' => 'integer',
        'limpiable_id' => 'integer',
    ];

    /** @return BelongsTo<LimpiezaHorario, $this> */
    public function horario(): BelongsTo
    {
        return $this->belongsTo(LimpiezaHorario::class, 'horario_id');
    }

    /** @return MorphTo<Model, $this> */
    public function limpiable(): MorphTo
    {
        return $this->morphTo();
    }
}
