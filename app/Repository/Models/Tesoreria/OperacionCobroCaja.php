<?php

declare(strict_types=1);

namespace App\Repository\Models\Tesoreria;

use App\Repository\Models\Cuentas\Venta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OperacionCobroCaja extends Model
{
    protected $table = 'tes_operaciones_cobro';

    protected $guarded = ['id'];

    /** @return BelongsTo<Venta, $this> */
    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }
}
