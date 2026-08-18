<?php

declare(strict_types=1);

namespace App\Repository\Models\Facturacion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

final class PasarelaPago extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'pasarelas_pago';

    protected $fillable = [
        'codigo',
        'nombre',
        'activa',
        'modo_prueba',
        'configuracion',
        'proveedor',
        'gestion',
    ];

    protected $attributes = [
        'activa' => true,
        'modo_prueba' => true,
    ];

    protected function casts(): array
    {
        return [
            'activa' => 'boolean',
            'modo_prueba' => 'boolean',
            'configuracion' => 'array',
        ];
    }

    /** @return HasMany<PagoTransaccion, $this> */
    public function transacciones(): HasMany
    {
        return $this->hasMany(PagoTransaccion::class);
    }
}
