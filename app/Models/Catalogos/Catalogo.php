<?php

namespace App\Models\Catalogos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;
class Catalogo extends Model implements AuditableContract
{
    /** @use HasFactory<\Database\Factories\PaisFactory> */
    use HasFactory, Auditable;
    protected $table = 'catalogos';
    protected $guarded = [];

    public function catalogoTipo()
    {
        return $this->belongsTo(CatalogoTipo::class, 'catalogo_tipo_id');
    }

    public function padre()
    {
        return $this->belongsTo(self::class, 'padre_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'padre_id');
    }
}