<?php

declare(strict_types=1);

namespace App\Repository\Models\Promociones;

use App\Enums\Promociones\EstadoUsoBeneficioCliente;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\Venta;
use App\Repository\Models\Reservas\Reserva;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class PromocionBeneficioUso extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'promocion_beneficio_usos';

    protected $guarded = ['id'];

    protected $casts = [
        'estado' => EstadoUsoBeneficioCliente::class,
        'monto_descuento' => 'decimal:2',
        'usado_en' => 'datetime',
        'metadata' => 'array',
    ];

    /** @return BelongsTo<PromocionBeneficio, $this> */
    public function beneficio(): BelongsTo
    {
        return $this->belongsTo(PromocionBeneficio::class, 'beneficio_id');
    }

    /** @return BelongsTo<Cliente, $this> */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /** @return BelongsTo<Reserva, $this> */
    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class);
    }

    /** @return BelongsTo<Cuenta, $this> */
    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(Cuenta::class);
    }

    /** @return BelongsTo<Venta, $this> */
    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }
}
