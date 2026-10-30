<?php

declare(strict_types=1);

namespace App\Repository\Models\Tesoreria;

use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

final class FondoTesoreria extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'tes_fondos';

    protected $guarded = ['id'];

    protected $casts = ['activo' => 'boolean', 'limite_vale' => 'decimal:2'];

    /** @return BelongsTo<Moneda, $this> */
    public function moneda(): BelongsTo
    {
        return $this->belongsTo(Moneda::class, 'moneda_id');
    }

    /** @return BelongsTo<User, $this> */
    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    /** @return HasMany<MovimientoFondo,$this> */
    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoFondo::class, 'fondo_id');
    }
}
