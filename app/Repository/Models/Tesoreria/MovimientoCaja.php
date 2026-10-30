<?php

declare(strict_types=1);

namespace App\Repository\Models\Tesoreria;

use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

final class MovimientoCaja extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'tes_movimientos_caja';

    protected $guarded = ['id'];

    protected $casts = ['importe' => 'decimal:2'];

    protected static function booted(): void
    {
        self::updating(fn () => throw new \DomainException('El movimiento de caja es inmutable.'));
        self::deleting(fn () => throw new \DomainException('El movimiento de caja no se elimina.'));
    }

    /** @return BelongsTo<Moneda, $this> */
    public function moneda(): BelongsTo
    {
        return $this->belongsTo(Moneda::class);
    }

    /** @return BelongsTo<User, $this> */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
