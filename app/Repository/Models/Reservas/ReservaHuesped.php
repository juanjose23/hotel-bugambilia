<?php

declare(strict_types=1);

namespace App\Repository\Models\Reservas;

use App\Enums\Reservas\TipoHuesped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ReservaHuesped extends Model
{
    protected $table = 'reserva_huespedes';

    protected $guarded = ['id'];

    protected $casts = [
        'tipo_identificacion' => 'integer',
        'tipo_huesped' => TipoHuesped::class,
        'es_titular' => 'boolean',
        'fecha_nacimiento' => 'date',
    ];

    /** @return BelongsTo<ReservaDetalle, $this> */
    public function detalle(): BelongsTo
    {
        return $this->belongsTo(ReservaDetalle::class, 'reserva_detalle_id');
    }
}
