<?php

declare(strict_types=1);

namespace App\Repository\Models\Promociones;

use App\Enums\Promociones\TipoReglaBeneficioCliente;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class PromocionBeneficioRegla extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'promocion_beneficio_reglas';

    protected $guarded = ['id'];

    protected $casts = [
        'tipo_regla' => TipoReglaBeneficioCliente::class,
        'valor_numerico' => 'decimal:2',
        'obligatoria' => 'boolean',
    ];

    /** @return BelongsTo<PromocionBeneficio, $this> */
    public function beneficio(): BelongsTo
    {
        return $this->belongsTo(PromocionBeneficio::class, 'beneficio_id');
    }
}
