<?php

declare(strict_types=1);

namespace App\Repository\Models\Personas;

use App\Repository\Models\Catalogos\Pais;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Compras\Proveedor;
use App\Repository\Models\User;
use App\Repository\Queries\Shared\ObtenerNombrePersona;
use Database\Factories\PersonaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/** @use HasFactory<PersonaFactory> */
class Persona extends Model implements AuditableContract
{
    /** @phpstan-ignore missingType.generics */
    use Auditable, HasFactory, SoftDeletes;

    protected static function newFactory(): PersonaFactory
    {
        return PersonaFactory::new();
    }

    protected $table = 'personas';

    protected $appends = [
        'nombre_completo',
    ];

    protected $with = [
        'personaNatural',
        'personaJuridica',
    ];

    public function getNombreCompletoAttribute(): ?string
    {
        $resultado = ObtenerNombrePersona::desde($this);

        return filled($resultado) && ! str_starts_with($resultado, 'Persona #')
            ? $resultado
            : null;
    }

    /**
     * @param  Builder<Persona>  $query
     * @return Builder<Persona>
     */
    public function scopeConNombre(Builder $query, string $busqueda): Builder
    {
        return self::filtrarPorNombre($query, $busqueda);
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public static function filtrarPorNombre(Builder $query, string $busqueda): Builder
    {
        $terminos = preg_split('/\s+/', trim($busqueda), flags: PREG_SPLIT_NO_EMPTY) ?: [];

        foreach ($terminos as $termino) {
            $query->where(function (Builder $nombreQuery) use ($termino): void {
                $nombreQuery
                    ->whereLike('primer_nombre', "%{$termino}%")
                    ->orWhereLike('segundo_nombre', "%{$termino}%")
                    ->orWhereHas('personaNatural', function (Builder $naturalQuery) use ($termino): void {
                        $naturalQuery
                            ->whereLike('primer_apellido', "%{$termino}%")
                            ->orWhereLike('segundo_apellido', "%{$termino}%");
                    })
                    ->orWhereHas('personaJuridica', fn (Builder $juridicaQuery): Builder => $juridicaQuery
                        ->whereLike('razon_social', "%{$termino}%"));
            });
        }

        return $query;
    }

    protected $guarded = ['id'];

    /** @return BelongsTo<Pais, $this> */
    public function pais(): BelongsTo
    {
        return $this->belongsTo(Pais::class);
    }

    /** @return HasOne<Colaborador, $this> */
    public function colaborador(): HasOne
    {
        return $this->hasOne(Colaborador::class);
    }

    /** @return HasOne<Proveedor, $this> */
    public function proveedor(): HasOne
    {
        return $this->hasOne(Proveedor::class);
    }

    /** @return HasOne<Cliente, $this> */
    public function cliente(): HasOne
    {
        return $this->hasOne(Cliente::class);
    }

    /** @return HasOne<User, $this> */
    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    /** @return HasOne<PersonaNatural, $this> */
    public function personaNatural(): HasOne
    {
        return $this->hasOne(PersonaNatural::class);
    }

    /** @return HasOne<PersonaJuridica, $this> */
    public function personaJuridica(): HasOne
    {
        return $this->hasOne(PersonaJuridica::class);
    }
}
