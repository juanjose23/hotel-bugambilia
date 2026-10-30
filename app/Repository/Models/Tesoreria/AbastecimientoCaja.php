<?php

declare(strict_types=1);

namespace App\Repository\Models\Tesoreria;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

final class AbastecimientoCaja extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'tes_abastecimientos';

    protected $guarded = ['id'];

    protected $casts = ['importe' => 'decimal:2'];

    /** @return BelongsTo<FondoTesoreria,$this> */
    public function fondo(): BelongsTo
    {
        return $this->belongsTo(FondoTesoreria::class);
    }

    /** @return BelongsTo<SesionCaja,$this> */
    public function sesion(): BelongsTo
    {
        return $this->belongsTo(SesionCaja::class);
    }
}
