<?php

namespace App\Models\Colaboradores;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class ColaboradorContactoEmergencia extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'colaborador_contactos_emergencia';

    protected $guarded = [];

    /** @return BelongsTo<Colaborador, $this> */
    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class);
    }
}
