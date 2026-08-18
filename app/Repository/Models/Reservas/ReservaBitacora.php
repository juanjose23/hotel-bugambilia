<?php

declare(strict_types=1);

namespace App\Repository\Models\Reservas;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ReservaBitacora extends Model
{
    protected $table = 'reservas_bitacora';

    protected $fillable = [
        'reserva_id',
        'tipo',
        'datos',
    ];

    protected function casts(): array
    {
        return [
            'datos' => 'array',
        ];
    }

    /** @return BelongsTo<Reserva, $this> */
    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class);
    }
}
