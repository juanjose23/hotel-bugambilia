<?php

namespace App\Repository\Models\Personas;

use App\Repository\Models\Catalogos\Pais;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Compras\Proveedor;
use App\Repository\Models\User;
use Database\Factories\PersonaFactory;
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
        $nombre = trim(
            ($this->primer_nombre ?? '').' '.
            ($this->segundo_nombre ?? '')
        );
        $apellido = trim(
            ($this->personaNatural->primer_apellido ?? '').' '.
            ($this->personaNatural->segundo_apellido ?? '')
        );

        $resultado = trim($nombre.' '.$apellido);

        return $resultado !== '' ? $resultado : null;
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
