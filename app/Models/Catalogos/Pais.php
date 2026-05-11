<?php

namespace App\Models\Catalogos;

use App\Models\Personas\Persona;
use Database\Factories\PaisFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Pais extends Model implements AuditableContract
{
    /** @use HasFactory<PaisFactory> */
    use Auditable, HasFactory;

    protected $table = 'paises';

    protected $guarded = [];

    /** @return HasMany<Persona, $this> */
    public function personas(): HasMany
    {
        return $this->hasMany(Persona::class, 'pais_id');
    }
}
