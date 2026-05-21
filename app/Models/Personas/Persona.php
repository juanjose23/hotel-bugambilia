<?php

namespace App\Models\Personas;

use App\Models\Catalogos\Pais;
use App\Models\Colaboradores\Colaborador;
use Database\Factories\PersonaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Persona extends Model implements AuditableContract
{
    /** @use HasFactory<PersonaFactory> */
    use Auditable, HasFactory, SoftDeletes;

    protected static function newFactory(): PersonaFactory
    {
        return PersonaFactory::new();
    }

    protected $table = 'personas';

    protected $with = [
        'personaNatural',
        'personaJuridica',
    ];

    protected $fillable = [
        'primer_nombre',
        'segundo_nombre',
        'pais_id',
        'tipo_persona',
        'telefono',
        'direccion',
    ];

    /** @return BelongsTo<Pais, $this> */
    public function pais(): BelongsTo
    {
        return $this->belongsTo(Pais::class);
    }

    /** @return hasOne<Colaborador,$this>*/
    public function colaborador(): HasOne
    {
        return $this->hasOne(Colaborador::class);
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
