<?php

declare(strict_types=1);

namespace App\Models\Habitaciones;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class HabitacionHistorial extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'habitacion_historial';

    protected $fillable = [
        'model_type',
        'model_id',
        'estado_anterior',
        'estado_nuevo',
        'usuario_id',
        'comentario',
    ];

    protected $casts = [
        'model_id' => 'integer',
        'usuario_id' => 'integer',
    ];

    /** @return BelongsTo<User, $this> */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
