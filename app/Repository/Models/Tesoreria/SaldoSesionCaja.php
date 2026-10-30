<?php

declare(strict_types=1);

namespace App\Repository\Models\Tesoreria;

use App\Repository\Models\Monedas\Moneda;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

final class SaldoSesionCaja extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'tes_saldos_sesion';

    protected $guarded = ['id'];

    protected $casts = ['apertura' => 'decimal:2', 'esperado_cierre' => 'decimal:2', 'contado' => 'decimal:2', 'diferencia' => 'decimal:2'];

    protected static function booted(): void
    {
        self::updating(function (self $saldo): void {
            if ($saldo->isDirty(['apertura', 'moneda_id', 'sesion_id'])) {
                throw new \DomainException('El fondo inicial del turno no puede modificarse.');
            }
        });
        self::deleting(fn () => throw new \DomainException('El conteo de un turno no se elimina.'));
    }

    /** @return BelongsTo<Moneda, $this> */
    public function moneda(): BelongsTo
    {
        return $this->belongsTo(Moneda::class);
    }
}
