<?php

declare(strict_types=1);

namespace App\Repository\Models\Facturacion;

use App\Enums\Facturacion\EstadoFolioFactura;
use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

final class FacturaFolio extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'factura_folios';

    protected $guarded = ['id'];

    protected $attributes = [
        'estado' => 1,
    ];

    protected function casts(): array
    {
        return [
            'estado' => EstadoFolioFactura::class,
            'numero_correlativo' => 'integer',
            'reservado_at' => 'datetime',
            'emitido_at' => 'datetime',
            'anulado_at' => 'datetime',
            'fallido_at' => 'datetime',
            'meta_datos' => 'array',
        ];
    }

    /** @return BelongsTo<FacturaSerie, $this> */
    public function serie(): BelongsTo
    {
        return $this->belongsTo(FacturaSerie::class, 'factura_serie_id');
    }

    /** @return BelongsTo<FacturaAutorizacionDgi, $this> */
    public function autorizacionDgi(): BelongsTo
    {
        return $this->belongsTo(FacturaAutorizacionDgi::class, 'factura_autorizacion_dgi_id');
    }

    /** @return BelongsTo<Factura, $this> */
    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class);
    }

    /** @return BelongsTo<User, $this> */
    public function usuarioReserva(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reservado_por');
    }
}
