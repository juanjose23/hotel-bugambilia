<?php

declare(strict_types=1);

namespace App\Models\Activos;

use App\Enums\Activos\EstadoMantenimiento;
use App\Enums\Activos\TipoMantenimiento;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @property TipoMantenimiento $tipo
 */
class ActivoMantenimiento extends Model implements Auditable
{
    /** @use HasFactory<Factory<static>> */
    use AuditableTrait, HasFactory, SoftDeletes;

    protected $table = 'inv_mantenimientos';

    protected $guarded = ['id'];

    protected $casts = [
        'estado' => EstadoMantenimiento::class,
        'tipo' => TipoMantenimiento::class,
        'fecha_programada' => 'date',
        'fecha_realizada' => 'date',
        'costo_real' => 'decimal:2',
    ];

    /**
     * @return BelongsTo<Activo, $this>
     */
    public function activo(): BelongsTo
    {
        return $this->belongsTo(Activo::class, 'activo_id');
    }

    /**
     * @return BelongsTo<ActPlanMantenimiento, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(ActPlanMantenimiento::class, 'plan_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function realizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'realizado_por_id');
    }
}
