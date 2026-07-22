<?php

namespace App\Repository\Models\Catalogos;

use App\Repository\Models\Personas\Persona;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Pais extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'paises';

    protected $guarded = ['id'];

    /** @return HasMany<Persona, $this> */
    public function personas(): HasMany
    {
        return $this->hasMany(Persona::class, 'pais_id');
    }
}
