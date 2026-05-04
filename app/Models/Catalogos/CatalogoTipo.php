<?php

namespace App\Models\Catalogos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;
class CatalogoTipo extends Model  implements AuditableContract
{
    /** @use HasFactory<\Database\Factories\PaisFactory> */
    use HasFactory, Auditable;
    protected $table = 'catalogo_tipos';
    protected $guarded = [];
}
