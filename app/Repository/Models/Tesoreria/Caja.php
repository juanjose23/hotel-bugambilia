<?php

declare(strict_types=1);

namespace App\Repository\Models\Tesoreria;

use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Facturacion\FacturaSerie;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

final class Caja extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'tes_cajas';

    protected $guarded = ['id'];

    protected $casts = [
        'activa' => 'boolean',
        'ubicacion_id' => 'integer',
        'tipo_caja_id' => 'integer',
        'creada_por' => 'integer',
    ];

    /** @return BelongsTo<Ubicacion, $this> */
    public function ubicacionRelacionada(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_id');
    }

    /** @return BelongsTo<Catalogo, $this> */
    public function tipoCaja(): BelongsTo
    {
        return $this->belongsTo(Catalogo::class, 'tipo_caja_id');
    }

    /** @return BelongsTo<User, $this> */
    public function creadaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creada_por');
    }

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

    /** @return HasMany<FacturaSerie, $this> */
    public function seriesFiscales(): HasMany
    {
        return $this->hasMany(FacturaSerie::class, 'caja_id');
    }

    /** @return HasManyThrough<AbastecimientoCaja, SesionCaja, $this> */
    public function abastecimientos(): HasManyThrough
    {
        return $this->hasManyThrough(AbastecimientoCaja::class, SesionCaja::class, 'caja_id', 'sesion_id');
    }

    /** @return HasManyThrough<EntregaCaja, SesionCaja, $this> */
    public function entregas(): HasManyThrough
    {
        return $this->hasManyThrough(EntregaCaja::class, SesionCaja::class, 'caja_id', 'sesion_id');
    }

    /** @return HasManyThrough<DevolucionCaja, SesionCaja, $this> */
    public function devoluciones(): HasManyThrough
    {
        return $this->hasManyThrough(DevolucionCaja::class, SesionCaja::class, 'caja_id', 'sesion_id');
    }
}
