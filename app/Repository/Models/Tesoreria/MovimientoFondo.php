<?php

declare(strict_types=1);

namespace App\Repository\Models\Tesoreria;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

final class MovimientoFondo extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'tes_movimientos_fondo';

    protected $guarded = ['id'];

    protected $casts = ['importe' => 'decimal:2'];

    protected static function booted(): void
    {
        self::updating(fn () => throw new \DomainException('Este registro de auditoría es inmutable.'));
        self::deleting(fn () => throw new \DomainException('Este registro de auditoría se conserva.'));
    }

    /** @return BelongsTo<FondoTesoreria, $this> */
    public function fondo(): BelongsTo
    {
        return $this->belongsTo(FondoTesoreria::class, 'fondo_id');
    }
}
