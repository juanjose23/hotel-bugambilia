<?php

declare(strict_types=1);

namespace App\Repository\Models\Restaurante;

use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $user_id
 * @property int|null $mesa_id
 * @property int|null $pedido_id
 * @property string $accion
 * @property array<string, mixed>|null $detalles
 * @property string|null $ip
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class AuditoriaRestaurante extends Model
{
    protected $table = 'auditoria_restaurante';

    protected $guarded = ['id'];

    protected $casts = [
        'detalles' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /** @return BelongsTo<User, $this> */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return BelongsTo<Espacio, $this> */
    public function mesa(): BelongsTo
    {
        return $this->belongsTo(Espacio::class, 'mesa_id');
    }

    /** @return BelongsTo<Pedido, $this> */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }
}
