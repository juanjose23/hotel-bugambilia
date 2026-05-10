<?php

namespace App\Models\Catalogos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Ubicacion extends Model implements AuditableContract
{
    //
    use Auditable, SoftDeletes;

    protected $table = 'ubicaciones';

    protected $fillable = [
        'nombre',
        'descripcion',
        'tipo',
        'orden',
        'padre_id',
        'estado',
    ];

    /**
     * @return BelongsTo<self, $this>
     */
    public function padre(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'padre_id');
    }
}
