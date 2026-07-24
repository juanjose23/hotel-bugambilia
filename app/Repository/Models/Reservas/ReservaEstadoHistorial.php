<?php

declare(strict_types=1);

namespace App\Repository\Models\Reservas;

use App\Enums\Reservas\EstadoReserva;
use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ReservaEstadoHistorial extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'reserva_estado_historial';

    protected $guarded = ['id'];

    protected $casts = [
        'estado_anterior' => EstadoReserva::class,
        'estado_nuevo' => EstadoReserva::class,
        'created_at' => 'datetime',
    ];

    /** @return BelongsTo<Reserva, $this> */
    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class);
    }

    /** @return BelongsTo<User, $this> */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
