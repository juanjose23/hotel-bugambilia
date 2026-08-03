<?php

namespace App\Repository\Models\Personas;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class PersonaNatural extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;
    use Auditable, SoftDeletes;

    protected $table = 'personas_naturales';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
        ];
    }

    /** @return BelongsTo<Persona, $this> */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }

    public function getFullNameAttribute(): string
    {
        $persona = $this->relationLoaded('persona') ? $this->persona : $this->persona()->first();

        $parts = [
            $persona->primer_nombre ?? '',
            $persona->segundo_nombre ?? '',
            $this->primer_apellido ?? '',
            $this->segundo_apellido ?? '',
        ];

        return trim(preg_replace('/\s+/', ' ', implode(' ', array_filter($parts))) ?? '');
    }
}
