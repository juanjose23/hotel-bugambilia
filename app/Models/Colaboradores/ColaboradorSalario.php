<?php

namespace App\Models\Colaboradores;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class ColaboradorSalario extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'colaborador_salarios';

    protected $guarded = ['id'];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'estado' => 'integer',
    ];

    /** @return BelongsTo<Colaborador, $this> */
    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class);
    }
}
