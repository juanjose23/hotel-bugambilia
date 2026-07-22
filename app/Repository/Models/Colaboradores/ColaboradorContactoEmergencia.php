<?php

namespace App\Repository\Models\Colaboradores;

use App\Enums\Shared\EstadoGeneral;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class ColaboradorContactoEmergencia extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'colaborador_contactos_emergencia';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'estado' => EstadoGeneral::class,
        ];
    }

    /** @return BelongsTo<Colaborador, $this> */
    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class);
    }
}
