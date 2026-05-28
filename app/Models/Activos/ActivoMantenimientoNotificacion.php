<?php

declare(strict_types=1);

namespace App\Models\Activos;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class ActivoMantenimientoNotificacion extends Model implements Auditable
{
    /** @use HasFactory<Factory> */
    use AuditableTrait, HasFactory;

    protected $table = 'inv_mantenimiento_notificaciones';

    protected $guarded = ['id'];

    public $timestamps = false;

    protected $casts = [
        'enviado_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * @return BelongsTo<ActivoMantenimiento, $this>
     */
    public function mantenimiento(): BelongsTo
    {
        return $this->belongsTo(ActivoMantenimiento::class, 'mantenimiento_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function enviadoA(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enviado_a');
    }
}
