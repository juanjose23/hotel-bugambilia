<?php

namespace App\Models\Personas;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
class PersonaNatural extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;
    protected $table = 'personas_naturales';
    protected $guarded = [];


    /** @return BelongsTo<Persona, $this> */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }

    public function getFullNameAttribute(): string
    {
        $parts = [
            $this->primer_nombre ?? '',
            $this->segundo_nombre ?? '',
            $this->primer_apellido ?? '',
            $this->segundo_apellido ?? '',
        ];

        return trim(preg_replace('/\s+/', ' ', implode(' ', array_filter($parts))));
    }
}
