<?php

namespace App\Models\Personas;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class PersonaJuridica extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'personas_juridicas';

    protected $guarded = ['id'];

    /** @return BelongsTo<Persona, $this> */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }
}
