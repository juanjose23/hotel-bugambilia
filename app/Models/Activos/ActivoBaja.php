<?php

declare(strict_types=1);

namespace App\Models\Activos;

use App\Enums\Activos\TipoBaja;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class ActivoBaja extends Model implements Auditable
{
    /** @use HasFactory<Factory<static>> */
    use AuditableTrait, HasFactory, SoftDeletes;

    protected $table = 'inv_activo_bajas';

    protected $guarded = ['id'];

    protected $casts = [
        'motivo_tipo' => TipoBaja::class,
        'fecha_baja' => 'date',
        'valor_residual' => 'decimal:2',
    ];

    /**
     * @return BelongsTo<Activo, $this>
     */
    public function activo(): BelongsTo
    {
        return $this->belongsTo(Activo::class, 'activo_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function aprobadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobado_por_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por_id');
    }
}
