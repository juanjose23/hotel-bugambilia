<?php

declare(strict_types=1);

namespace App\Repository\Models\Reservas;

use App\Repository\Models\Servicios\Servicio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservaServicio extends Model
{
    protected $table = 'reserva_servicios';

    protected $guarded = ['id'];

    protected $casts = [
        'cantidad' => 'integer',
        'precio' => 'decimal:2',
    ];

    /**
     * @return BelongsTo<Reserva, $this>
     */
    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    /**
     * @return BelongsTo<Servicio, $this>
     */
    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }
}
