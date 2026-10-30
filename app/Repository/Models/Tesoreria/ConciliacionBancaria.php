<?php

declare(strict_types=1);

namespace App\Repository\Models\Tesoreria;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

final class ConciliacionBancaria extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'tes_conciliaciones';

    protected $guarded = ['id'];

    protected $casts = ['comision' => 'decimal:2'];

    protected static function booted(): void
    {
        self::updating(fn () => throw new \DomainException('Este registro de auditoría es inmutable.'));
        self::deleting(fn () => throw new \DomainException('Este registro de auditoría se conserva.'));
    }

    /** @return BelongsTo<ExtractoBancario, $this> */
    public function extracto(): BelongsTo
    {
        return $this->belongsTo(ExtractoBancario::class, 'extracto_id');
    }

    /** @return BelongsTo<MovimientoFondo, $this> */
    public function movimiento(): BelongsTo
    {
        return $this->belongsTo(MovimientoFondo::class, 'movimiento_id');
    }
}
