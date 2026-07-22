<?php

namespace App\Repository\Models\Colaboradores;

use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Catalogos\Catalogo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class ColaboradorCargoHistorial extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'colaborador_cargos_historial';

    protected $guarded = ['id'];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'estado' => EstadoGeneral::class,
    ];

    /** @return BelongsTo<Colaborador, $this> */
    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class);
    }

    /** @return BelongsTo<Catalogo, $this> */
    public function cargo(): BelongsTo
    {
        return $this->belongsTo(Catalogo::class, 'cargo_id');
    }

    /** @return BelongsTo<Catalogo, $this> */
    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Catalogo::class, 'departamento_id');
    }
}
