<?php

declare(strict_types=1);

namespace App\Repository\Models\Tesoreria;

use App\Repository\Models\Monedas\Moneda;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

final class Caja extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'tes_cajas';

    protected $guarded = ['id'];

    protected $casts = ['activa' => 'boolean'];

    /** @return BelongsToMany<Moneda, $this> */
    public function monedas(): BelongsToMany
    {
        return $this->belongsToMany(Moneda::class, 'tes_caja_monedas', 'caja_id', 'moneda_id');
    }

    /** @return HasOne<SesionCaja, $this> */
    public function sesionActiva(): HasOne
    {
        return $this->hasOne(SesionCaja::class, 'caja_id')->whereIn('estado', ['abierta', 'pendiente_revision']);
    }

    /** @return HasMany<SesionCaja, $this> */
    public function sesiones(): HasMany
    {
        return $this->hasMany(SesionCaja::class, 'caja_id');
    }
}
