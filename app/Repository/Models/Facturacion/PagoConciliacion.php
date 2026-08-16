<?php

declare(strict_types=1);

namespace App\Repository\Models\Facturacion;

use App\Enums\Facturacion\EstadoConciliacionPago;
use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @property EstadoConciliacionPago $estado
 */
final class PagoConciliacion extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'pago_conciliaciones';

    protected $guarded = ['id'];

    protected $attributes = [
        'estado' => 1,
        'monto_esperado' => 0,
        'monto_recibido' => 0,
        'diferencia' => 0,
    ];

    protected function casts(): array
    {
        return [
            'estado' => EstadoConciliacionPago::class,
            'monto_esperado' => 'decimal:2',
            'monto_recibido' => 'decimal:2',
            'diferencia' => 'decimal:2',
            'conciliada_at' => 'datetime',
            'meta_datos' => 'array',
        ];
    }

    /** @return BelongsTo<PagoTransaccion, $this> */
    public function transaccion(): BelongsTo
    {
        return $this->belongsTo(PagoTransaccion::class, 'pago_transaccion_id');
    }

    /** @return BelongsTo<User, $this> */
    public function conciliador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'conciliada_por');
    }
}
