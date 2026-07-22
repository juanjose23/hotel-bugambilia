<?php

namespace App\Repository\Models\Catalogos;

use App\Enums\Shared\EstadoGeneral;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class CatalogoTipo extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'catalogo_tipos';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'estado' => EstadoGeneral::class,
        ];
    }
}
