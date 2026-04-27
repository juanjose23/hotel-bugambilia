<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class Persona extends Model implements AuditableContract
{
    /** @use HasFactory<\Database\Factories\PersonaFactory> */
    use HasFactory, SoftDeletes, Auditable;
    protected $table = 'personas';
    protected $guarded = [];

    /** @return BelongsTo<Pais, $this> */
    public function pais(): BelongsTo
    {
        return $this->belongsTo(Pais::class);
    }
}