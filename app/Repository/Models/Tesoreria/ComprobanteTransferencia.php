<?php

declare(strict_types=1);

namespace App\Repository\Models\Tesoreria;

use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

final class ComprobanteTransferencia extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'tes_comprobantes';

    protected $guarded = ['id'];

    protected $casts = ['importe' => 'decimal:2', 'fecha' => 'date', 'revisado_at' => 'datetime'];

    /** @return BelongsTo<FondoTesoreria, $this> */
    public function fondo(): BelongsTo
    {
        return $this->belongsTo(FondoTesoreria::class, 'fondo_id');
    }

    /** @return BelongsTo<Cuenta, $this> */
    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(Cuenta::class, 'cuenta_id');
    }

    /** @return BelongsTo<User, $this> */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
