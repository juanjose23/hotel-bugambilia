<?php

declare(strict_types=1);

namespace App\Repository\Models\Clientes;

use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Promociones\PromocionBeneficioUso;
use App\Repository\Models\Reservas\Reserva;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @property-read string|null $nombre_completo
 * @property-read string|null $primer_nombre
 * @property-read string|null $telefono
 *
 * @method static self create(array<string, mixed> $attributes)
 * @method static self|null find($id)
 */
class Cliente extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'clientes';

    protected $guarded = ['id'];

    protected $casts = [
        'estado' => EstadoGeneral::class,
    ];

    /** @return BelongsTo<Persona, $this> */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }

    public function getNombreCompletoAttribute(): ?string
    {
        return $this->persona?->nombre_completo;
    }

    public function getPrimerNombreAttribute(): ?string
    {
        return $this->persona?->primer_nombre;
    }

    public function getTelefonoAttribute(): ?string
    {
        return $this->persona?->telefono;
    }

    /** @return BelongsTo<Catalogo, $this> */
    public function tipoCliente(): BelongsTo
    {
        return $this->belongsTo(Catalogo::class, 'catalogo_id');
    }

    /** @return HasMany<PromocionBeneficioUso, $this> */
    public function usosBeneficios(): HasMany
    {
        return $this->hasMany(PromocionBeneficioUso::class, 'cliente_id');
    }

    /** @return HasMany<Reserva, $this> */
    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'cliente_id');
    }

    /** @return HasMany<TarjetaCliente, $this> */
    public function tarjetas(): HasMany
    {
        return $this->hasMany(TarjetaCliente::class, 'cliente_id');
    }

    public function stripeCustomerId(): ?string
    {
        return $this->stripe_customer_id;
    }
}
