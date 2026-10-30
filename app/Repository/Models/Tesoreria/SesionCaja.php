<?php

declare(strict_types=1);

namespace App\Repository\Models\Tesoreria;

use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

final class SesionCaja extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'tes_sesiones_caja';

    protected $guarded = ['id'];

    protected $casts = ['abierta_at' => 'datetime', 'contada_at' => 'datetime', 'cerrada_at' => 'datetime'];

    /** @return BelongsTo<Caja, $this> */
    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class);
    }

    /** @return BelongsTo<User, $this> */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<SaldoSesionCaja, $this> */
    public function saldos(): HasMany
    {
        return $this->hasMany(SaldoSesionCaja::class, 'sesion_id');
    }

    /** @return HasMany<MovimientoCaja, $this> */
    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoCaja::class, 'sesion_id');
    }
}
