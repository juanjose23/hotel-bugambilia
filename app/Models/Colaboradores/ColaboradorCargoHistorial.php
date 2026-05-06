<?php

namespace App\Models\Colaboradores;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
class ColaboradorCargoHistorial extends Model implements AuditableContract
{
    use SoftDeletes, Auditable;
    protected $table = 'colaborador_cargos_historial';
    protected $guarded = [];
    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    /** @return BelongsTo<Colaborador, $this> */
    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class);
    }

    /** @return BelongsTo<\App\Models\Catalogos\Catalogo, $this> */
    public function cargo(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Catalogos\Catalogo::class, 'cargo_id');
    }

    /** @return BelongsTo<\App\Models\Catalogos\Catalogo, $this> */
    public function departamento(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Catalogos\Catalogo::class, 'departamento_id');
    }
}