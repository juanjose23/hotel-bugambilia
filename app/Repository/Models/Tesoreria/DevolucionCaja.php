<?php

declare(strict_types=1);

namespace App\Repository\Models\Tesoreria;

use App\Repository\Models\Cuentas\PagoCuenta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

final class DevolucionCaja extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'tes_devoluciones';

    protected $guarded = ['id'];

    protected $casts = ['importe' => 'decimal:2'];

    /** @return BelongsTo<PagoCuenta, $this> */
    public function pago(): BelongsTo
    {
        return $this->belongsTo(PagoCuenta::class, 'pago_cuenta_id');
    }

    /** @return BelongsTo<SesionCaja, $this> */
    public function sesion(): BelongsTo
    {
        return $this->belongsTo(SesionCaja::class, 'sesion_id');
    }
}
