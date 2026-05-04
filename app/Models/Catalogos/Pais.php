<?php

namespace App\Models\Catalogos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class Pais extends Model implements AuditableContract
{
    /** @use HasFactory<\Database\Factories\PaisFactory> */
    use HasFactory, Auditable;
    protected $table = 'paises';
    protected $guarded = [];

    /** @return HasMany<Persona, $this> */
    public function personas(): HasMany
    {
        return $this->hasMany(Persona::class, 'pais_id');
    }
}
