<?php

namespace App\Models\Catalogos;

use Database\Factories\CatalogoTipoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class CatalogoTipo extends Model implements AuditableContract
{
    /** @use HasFactory<CatalogoTipoFactory> */
    use Auditable, HasFactory;

    protected static function newFactory(): CatalogoTipoFactory
    {
        return CatalogoTipoFactory::new();
    }

    protected $table = 'catalogo_tipos';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'estado' => 'boolean',
        ];
    }
}
