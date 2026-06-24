<?php

declare(strict_types=1);

namespace App\Models\Limpieza;

use App\Models\Catalogos\Ubicacion;
use App\Models\Colaboradores\Colaborador;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Turno extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'limp_horario_turnos';

    protected $guarded = ['id'];

    protected $casts = [
        'lider_id' => 'integer',
        'apoyo_id' => 'integer',
        'carritos_ids' => 'array',
        'estado' => 'boolean',
    ];

    /**
     * @return BelongsTo<Colaborador, $this>
     */
    public function lider(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class, 'lider_id');
    }

    /**
     * @return BelongsTo<Colaborador, $this>
     */
    public function apoyo(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class, 'apoyo_id');
    }

    /**
     * @return Collection<int, Ubicacion>
     */
    public function getCarritosAttribute()
    {
        if (empty($this->carritos_ids)) {
            return collect();
        }

        return Ubicacion::whereIn('id', $this->carritos_ids)->get();
    }

    /**
     * @return HasMany<LimpiezaHorario, $this>
     */
    public function horarios(): HasMany
    {
        return $this->hasMany(LimpiezaHorario::class, 'turno_id');
    }

    /**
     * @return HasMany<LimpiezaEjecucion, $this>
     */
    public function ejecuciones(): HasMany
    {
        return $this->hasMany(LimpiezaEjecucion::class, 'turno_id');
    }
}
